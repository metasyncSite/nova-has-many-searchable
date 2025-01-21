import IndexField from './components/IndexField'
import DetailField from './components/DetailField'
import FormField from './components/FormField'

Nova.booting((app, store) => {
  app.component('index-has-many-searchable', IndexField)
  app.component('detail-has-many-searchable', DetailField)
  app.component('form-has-many-searchable', FormField)
})
