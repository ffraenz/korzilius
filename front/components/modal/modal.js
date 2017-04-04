
import React from 'react'

import Form from '../form/form'

export default class Modal extends React.Component {

  render () {
    this.form = null
    return (
      <div className="modal">
        <div className="modal__wrapper">
          <div className="modal__dialog">
            <div className="modal__header">
              <a
                className="modal__close-btn"
                onClick={evt => {
                  this.props.onDismiss()
                  evt.preventDefault()
                  return false
                }}
                href="#"
              >Zou maachen</a>
              <h3 className="modal__title">{this.props.title}</h3>
              { this.props.subtitle
                ? <span className="modal__subtitle">{this.props.subtitle}</span>
                : null }
            </div>
            <div className="modal__content">
              { this.props.formFields
                ? <Form
                    fields={this.props.formFields}
                    ref={form => this.form = form} />
                : null }
            </div>
            <div className="modal__footer">
              <a
                className="btn"
                onClick={evt => {
                  let data = this.form ? this.form.data : undefined
                  this.props.onSubmit(data)
                  evt.preventDefault()
                  return false
                }}
              >{this.props.submitLabel || 'Schécken'}</a>
            </div>
          </div>
        </div>
        <div className="modal__backdrop"></div>
      </div>
    )
  }
}
