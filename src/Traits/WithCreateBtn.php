<?php

declare(strict_types=1);

namespace MetasyncSite\NovaHasManySearchable\Traits;

trait WithCreateBtn
{
    protected bool $showCreateButton = false;

    protected string $createButtonLabel = 'Create New';

    public function withCreateButton(bool $show = true, ?string $label = null): static
    {
        $this->showCreateButton = $show;

        if ($label) {
            $this->createButtonLabel = $label;
        }

        $this->withMeta([
            'showCreateButton' => $this->showCreateButton,
            'createButtonLabel' => $this->createButtonLabel,
            'resourceName' => $this->resourceClass ? $this->resourceClass::uriKey() : null,
        ]);

        return $this;
    }

    protected function appendCreateButtonMeta(): void
    {
        $this->withMeta([
            'showCreateButton' => $this->showCreateButton,
            'createButtonLabel' => $this->createButtonLabel,
            'resourceName' => $this->resourceClass ? $this->resourceClass::uriKey() : null,
        ]);
    }

    /**
     * Override the parent resolve method
     */
    public function resolveUsing($callback): static
    {
        parent::resolveUsing($callback);
        $this->appendCreateButtonMeta();

        return $this;
    }
}
