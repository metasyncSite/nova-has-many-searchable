<?php

declare(strict_types=1);

namespace MetasyncSite\NovaHasManySearchable;

use Exception;
use Illuminate\Support\Facades\Log;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Database\Eloquent\Model;
use MetasyncSite\NovaHasManySearchable\Traits\WithCreateBtn;
use Throwable;

class HasManySearchable extends Field
{
    use WithCreateBtn;

    public $component = 'has-many-searchable';

    public $displayCallback;

    private mixed $resourceClass;

    private ?string $foreignKey;

    public function __construct($name, $attribute = null, $resolveCallback = null)
    {
        parent::__construct($name, $attribute, $resolveCallback);

        $this->withMeta([
            'options' => [],
            'placeholder' => 'Search...',
            'resourceClass' => null,
            'displayField' => 'name',
            'foreignKey' => null,
        ]);
    }

    /**
     * @param mixed $resource
     * @param string|null $attribute
     *
     * @throws Exception
     */
    public function resolve($resource, $attribute = null): void
    {
        if (!$this->resourceClass || !$this->foreignKey) {
            throw new Exception('relationshipConfig must be called with resourceClass and foreignKey');
        }

        $modelClass = $this->resourceClass::$model;

        if (request()->route('resource') && !request()->route('resourceId')) {
            $this->value = $resource->{$this->attribute}()->count();

            return;
        }

        if ($resource->exists) {
            $this->value = $modelClass::where($this->foreignKey, $resource->getKey())
                ->get()
                ->map(fn ($item) => $item->getKey())
                ->values()
                ->all();
        }

        $options = $modelClass::where(function ($query) use ($resource) {
            $query->whereNull($this->foreignKey)
                ->orWhere($this->foreignKey, $resource->getKey());
        })
            ->get()
            ->map(function ($item) {
                try {
                    if (!$item) {
                        return null;
                    }

                    if ($this->displayCallback) {
                        $label = call_user_func($this->displayCallback, $item);
                    } else {
                        $label = $item->{$this->meta['displayField']} ?? '';
                    }

                    if (empty($label)) {
                        $label = "ID: " . $item->getKey();
                    }

                    return [
                        'value' => $item->getKey(),
                        'label' => $label
                    ];
                } catch (Throwable $e) {
                    Log::error('HasManySearchable display error: ' . $e->getMessage());

                    return null;
                }
            })
            ->filter()
            ->values();

        $this->withMeta(['options' => $options]);
    }

    public function relationshipConfig(string $resourceClass, string $foreignKey, $displayCallback = null): static
    {
        $this->resourceClass = $resourceClass;
        $this->foreignKey = $foreignKey;
        $this->displayCallback = $displayCallback;

        return $this;
    }

    /**
     * @param NovaRequest $request
     * @param string $requestAttribute
     * @param Model $model
     * @param string $attribute
     *
     * @throws Throwable
     */
    protected function fillAttributeFromRequest(NovaRequest $request, $requestAttribute, $model, $attribute): void
    {
        if (!$request->exists($requestAttribute)) {
            return;
        }

        try {
            $modelClass = $this->resourceClass::$model;
            $selectedIds = json_decode($request->input($requestAttribute) ?? '[]', true);

            if (!is_array($selectedIds)) {
                $selectedIds = [];
            }

            $modelInstance = new $modelClass();
            $keyName = $modelInstance->getKeyName();

            $modelClass::whereIn($keyName, $selectedIds)
                ->update([$this->foreignKey => $model->getKey()]);

            $modelClass::where($this->foreignKey, $model->getKey())
                ->whereNotIn($keyName, $selectedIds)
                ->update([$this->foreignKey => null]);

        } catch (Throwable $e) {
            Log::error('HasManySearchable fillAttributeFromRequest error: ' . $e->getMessage());

            throw $e;
        }
    }

    public function resolveForDisplay($resource, $attribute = null): void
    {
        $count = $resource->{$this->attribute}()->count();

        $this->value = $count . ($count === 1 ? ' item' : ' items');
    }
}
