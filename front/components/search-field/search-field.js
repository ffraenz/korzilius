
import React from 'react'

export default class SearchField extends React.Component {

  constructor (props) {
    super(props)
  }

  render () {
    return (
      <div className="search-field">
        <input className="search-field__input" placeholder="Sichen" />
      </div>
    )
  }
}
