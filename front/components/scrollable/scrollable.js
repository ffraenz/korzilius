
import React from 'react'
import Loader from '../loader/loader'

const FLOW_DOWN = 'down';
const FLOW_UP = 'up';

export default class Scrollable extends React.Component {

  static get defaultProps() {
    return {
      classes: [],
      modifiers: [],
      flow: FLOW_DOWN,

      infiniteScrolling: false,
      onScrollEndReached: null,

      // scrollable tracks its context and scrolls
      // to initial position when it changes
      context: null
    }
  }

  constructor (props) {
    super(props)

    this.$scroll = null
    this.$tape = null
    this.$loader = null

    this.loaderHeight = 0
    this.maxScrollDistance = 0
    this.scrollingEnabled = true
    this.reachedScrollEnd = false
    this.context = null

    this.resizeHandler = this.onLayout.bind(this)

    // initial state
    this.state = {
      scrolling: false
    }
  }

  componentDidMount () {
    this.context = this.props.context

    window.addEventListener('resize', this.resizeHandler)
    this.onLayout()
  }

  onLayout (evt = null) {
    this.maxScrollDistance =
      this.$tape.getBoundingClientRect().height
      - this.$scroll.getBoundingClientRect().height

    if (this.props.infiniteScrolling) {
      this.loaderHeight = this.$loader.getBoundingClientRect().height
    }

    this.scrollingEnabled = (this.maxScrollDistance > 0)
  }

  onScroll (evt = null) {
    if (!this.scrollingEnabled) {
      return;
    }

    let scrollTop = this.$scroll.scrollTop

    // check if scrollable left its initial scroll position
    let scrolling = (
      this.props.flow === FLOW_UP && scrollTop < this.maxScrollDistance ||
      this.props.flow === FLOW_DOWN && scrollTop > 0
    )

    if (this.state.scrolling !== scrolling) {
      this.setState({ scrolling })
    }

    if (this.props.infiniteScrolling) {
      let reachedScrollEnd = ((
        this.props.flow === FLOW_DOWN &&
        scrollTop > this.maxScrollDistance - this.loaderHeight
      ) || (
        this.props.flow === FLOW_UP &&
        scrollTop < this.loaderHeight
      ))

      if (this.reachedScrollEnd !== reachedScrollEnd) {
        this.reachedScrollEnd = reachedScrollEnd
        if (reachedScrollEnd && this.props.onScrollEndReached !== null) {
          this.props.onScrollEndReached()
        }
      }
    }
  }

  componentDidUpdate () {
    // new content may have changed bounds and scroll position
    this.onLayout()

    // check if context has changed
    if (this.context !== this.props.context) {
      this.context = this.props.context

      // scroll to initial position when context of content changed
      if (this.props.flow === FLOW_DOWN) {
        this.$scroll.scrollTop = 0
      } else {
        this.$scroll.scrollTop = this.maxScrollDistance
      }

      // reset scrolling
      this.setState({
        scrolling: false
      })
    }
  }

  componentDidUnmount () {
    window.removeEventListener('resize', this.resizeHandler)
  }

  render () {
    // compose class names
    let className = ['scrollable'].concat(
      this.props.classes,
      this.props.modifiers.map(modifier => 'scrollable--' + modifier),
      'scrollable--flow-' + this.props.flow,
      this.state.scrolling ? 'scrollable--scrolling' : []
    ).join(' ')

    let content = this.props.children

    if (this.props.infiniteScrolling) {
      // create loader
      let loader = (
        <div className="scrollable__loader" ref={$el => this.$loader = $el}>
          <Loader />
        </div>
      )

      // depending on flow, append or prepend loader to content
      content = (this.props.flow === FLOW_DOWN
        ? [content, loader]
        : [loader, content])
    }

    return (
      <div className={className}>
        <div
          className="scrollable__scroll"
          ref={$el => this.$scroll = $el}
          onScroll={this.onScroll.bind(this)}>
          <div className="scrollable__tape" ref={$el => this.$tape = $el}>
            {content}
          </div>
        </div>
      </div>
    )
  }
}
