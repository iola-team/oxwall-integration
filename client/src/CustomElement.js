import React, { Component } from 'react';
import PropTypes from 'prop-types';
import { render, unmountComponentAtNode } from 'react-dom';
import retargetEvents from 'react-shadow-dom-retarget-events';

import customElement from './utils/customElement';

@customElement({
  tag: 'iola-custom',
  attrs: ['label'],
  methods: ['setCount'],
})
export default class Element extends Component {
  static propTypes = {
    label: PropTypes.string,
  };

  state = {
    count: 0,
    text: '',
  }

  setCount(count) {
    this.setState({ count });
  }

  onClick = () => {
    this.setState(({ count }) => ({
      count: count + 1,
    }));
  }

  onChange = (event) => {
    this.setState({ text: event.target.value });
  }

  render() {
    const { label } = this.props;
    const { count, text } = this.state;

    return (
      <>
        <button onClick={this.onClick}>Add</button>
        <input type="text" value={text} onChange={this.onChange} />
        <div>{label}: {count} - {text}</div>
      </>
    );
  }
}
