
import React from 'react'

const Message = props => {
  let message = props.message
  let isOtherSide = (message.senderClientId !== null)

  // compose class names
  let className = ['message'].concat(
    props.classes,
    props.modifiers.map(modifier => 'message--' + modifier),
    isOtherSide ? 'message--side-other' : []
  ).join(' ')

  return (
    <div className={className}>
      {message.text}
    </div>
  )
}

Message.defaultProps = {
  classes: [],
  modifiers: [],
  side: 'self'
}

export default Message
