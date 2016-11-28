
import React from 'react'
import ListItem from '../list-item/list-item'

const List = props => {
  // compose class name
  let className = ['list'].concat(
    props.classes,
    props.modifiers.map(modifier => 'list--' + modifier)
  ).join(' ')

  return (
    <ul className={className}>
      {props.items && props.items.map(item =>
        React.createElement(ListItem, item))}
      {props.children && props.children}
    </ul>
  )
}

List.defaultProps = {
  classes: [],
  modifiers: [],
}

export default List
