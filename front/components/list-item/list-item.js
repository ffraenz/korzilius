
import React from 'react'

const ListItem = props => {
  // compose class names
  let className = ['list-item'].concat(
    props.classes,
    props.modifiers.map(modifier => 'list-item--' + modifier),
    props.icon ? 'list-item--icon' : [],
    props.type === 'section' ? 'list-item--section' : []
  ).join(' ')

  // compose content
  let content = []

  if (props.type === 'section') {
    // section list item
    content.push(
      <div className="list-item__section">{props.title}</div>
    )
  } else {
    // default list item
    // having an icon
    if (props.icon) {
      content.push(
        <span className="list-item__icon">
          <span className={`icon icon-${props.icon}`}></span>
        </span>
      )
    }

    // having a title
    if (props.title) {
      content.push(
        <strong className="list-item__title">{props.title}</strong>
      )
    }

    // having text
    if (props.text) {
      content.push(
        <span className="list-item__text">{props.text}</span>
      )
    }
  }

  // wrap content inside content element
  content = (
    <div className="list-item__content">
      {content}
    </div>
  )

  // wrap content inside link element
  if (props.onClick || props.href) {
    content = (
      <a
        className="list-item__link"
        href={props.href ? props.href : null}
        onClick={(evt) => {
          evt.preventDefault()
          return props.onClick(evt)
        }}>
        {content}
      </a>
    )
  }

  return (
    <li className={className} key={props.key}>
      {content}
    </li>
  )
}

ListItem.defaultProps = {
  type: 'default',
  classes: [],
  modifiers: [],
}

export default ListItem
