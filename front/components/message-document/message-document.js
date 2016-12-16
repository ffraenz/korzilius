
import React from 'react'

const visibleFieldNames = [
  'txt_reference1',
  'txt_client_reference',
  'txt_client_reference2',
  'txt_agent_name',
  'txt_insur_name',
  'txt_contract_type',
  'txt_contract_nr',
  'txt_damage_type',
  'txt_damage_nr',
  'txt_offer_nr',
  'dat_avenant_start',
  'txt_status'
]

const hiddenFieldValuePairs = {
  'txt_agent_name': 'FF Friederes Sàrl'
}

const fieldLabelAliases = {
  'txt_agent_name': 'Agence',
  'txt_insur_name': 'Versicherung',
  'txt_contract_nr': 'Vertrag Nr',
  'txt_damage_nr': 'Schaden Nr'
}

export default class MessageDocument extends React.Component {

  constructor (props) {
    super(props)
  }

  render () {
    let message = this.props.message

    let fields = message.meta.mask.fields
      .filter(field => {
        return (
          hiddenFieldValuePairs[field.name] !== field.value &&
          visibleFieldNames.indexOf(field.name) !== -1
        )
      })
      .sort((a, b) => {
        return visibleFieldNames.indexOf(a.name) -
          visibleFieldNames.indexOf(b.name)
      })
      .map(field => {
        if (fieldLabelAliases[field.name] !== undefined) {
          field.label = fieldLabelAliases[field.name];
        }
        return field
      })

    let maskFields = fields.map(field => {


      return (
        <div className="message-document__field">
          <div className="message-document__label">
            {field.label}
          </div>
          <div className="message-document__value">
            {field.value}
          </div>
        </div>
      )
    })

    return (
      <div className="message-document">
        <h3 className="message-document__title">{message.meta.mask.label}</h3>
        <div className="message-document__mask">
          {maskFields}
        </div>
      </div>
    )
  }
}
