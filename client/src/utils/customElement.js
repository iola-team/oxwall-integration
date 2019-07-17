import React from 'react';
import ReactDom from 'react-dom';
import camelCase from 'camelcase';

const extractAttrs = (attrs, element) => attrs.reduce((props, attrName) => {
  props[camelCase(attrName)] = element.getAttribute(attrName);

  return props;
}, {});

export default (options = {}) => Component => {
  const observedAttributes = options.attrs || [];
  let shadowRoot = null;
  let componentInstance = null;

  const render = element => ReactDom.render(
    React.createElement(Component, extractAttrs(observedAttributes, element)),
    shadowRoot,
    function () {
      componentInstance = this;
    }
  );

  class CustomElement extends HTMLElement {
    static observedAttributes = observedAttributes;

    constructor(...args) {
      super(...args);

      shadowRoot = this.attachShadow({ mode: 'open' });
    }

    connectedCallback() {
      render(this);
    }

    attributeChangedCallback() {
      render(this);
    }

    disconnectedCallback() {
      ReactDom.unmountComponentAtNode(shadowRoot);
    }
  }

  if (options.methods) {
    Object.assign(CustomElement.prototype, options.methods.reduce((methods, methodName) => ({
      ...methods,
      [methodName]: (...args) => componentInstance ?.[methodName] ?.(...args),
    }), {}));
  }

  if (options.tag) {
    customElements.define(options.tag, CustomElement);
  }

  return CustomElement;
};
