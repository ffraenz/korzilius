
import React from 'react'
import MessageDocument from '../message-document/message-document'

const Message = props => {
  let message = props.message
  let isOtherSide = (message.senderClientId !== null)

  // compose class names
  let className = ['message'].concat(
    props.classes,
    props.modifiers.map(modifier => 'message--' + modifier),
    isOtherSide ? 'message--side-other' : []
  ).join(' ')

  let content = null
  let icon = null

  if (message.type === 'document') {
    icon = <div className="message__icon message__icon--document"></div>
    content = <MessageDocument message={message} />
  }

  if (content === null) {
    content = (
      <span className="message__text">
        {message.text}
      </span>
    )
  }

  return (
    <div className={className} key={message.id}>
      {icon}
      {content}
    </div>
  )
}

Message.defaultProps = {
  classes: [],
  modifiers: [],
  side: 'self'
}

export default Message
