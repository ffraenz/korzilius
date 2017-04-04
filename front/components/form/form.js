
import React from 'react'

import FormSelect from '../form-select/form-select'
import FormRadio from '../form-radio/form-radio'
import FormText from '../form-text/form-text'

let fieldTypeClassMap = {
  select: FormSelect,
  radio: FormRadio,
  text: FormText
}

export default class Form extends React.Component {

  constructor (props) {
    super(props)
  }

  get data () {
    return Object.keys(this.fields)
      .map(name => this.fields[name].value)
  }

  onChange (field, value) {
    if (this.props.onChange) {
      this.props.onChange(this, this.data)
    }
  }

  render () {
    this.fields = []

    let label = this.props.labels
    return (
      <div className="form">
        { this.props.fields.map(
          (field, index) => {
            let type = field.type || 'text'
            let fieldClass = fieldTypeClassMap[type]
            let fieldProps = Object.assign({
              ref: (instance => this.fields[field.name] = instance),
              onChange: this.onChange.bind(this)
            }, field)

            return (
              <div className="form__field">
                { field.label
                  ? <label className="form__label">{field.label}</label>
                  : null }
                { React.createElement(fieldClass, fieldProps) }
                { field.help
                  ? <span className="form__help">{field.help}</span>
                  : null }
              </div>
            )
          }
        ) }
      </div>
    )
  }
}
