import React from 'react';
import ReactDom from 'react-dom';
import retargetEvents from 'react-shadow-dom-retarget-events';

const extractAttrs = (attrs, element) => attrs.reduce((props, attrName) => {
  const camelCasedName = attrName.replace(/-([a-z])/g, (r) => r[1].toUpperCase());
  props[camelCasedName] = element.getAttribute(attrName);

  return props;
}, {});

export default (options = {}) => Component => {
  const mountPoint = document.createElement('div');
  let componentInstance = null;
  const observedAttributes = options.attrs || [];
  const render = element => ReactDom.render(
    React.createElement(Component, extractAttrs(observedAttributes, element)),
    mountPoint,
    function () {
      componentInstance = this;
    }
  );

  class CustomElement extends HTMLElement {
    static observedAttributes = options.attrs || [];

    connectedCallback() {
      const shadowRoot = this.attachShadow({ mode: 'open' });

      shadowRoot.appendChild(mountPoint);
      render(this);
      retargetEvents(shadowRoot);
    }

    disconnectedCallback() {
      ReactDom.unmountComponentAtNode(mountPoint);
    }

    attributeChangedCallback(attrName, oldValue, newValue) {
      render(this);
    }
  }

  if (options.tag) {
    customElements.define(options.tag, CustomElement);
  }

  if (options.methods) {
    Object.assign(CustomElement.prototype, options.methods.reduce((methods, methodName) => ({
      ...methods,
      [methodName]: (...args) => componentInstance ?.[methodName] ?.(...args),
    }), {}));
  }

  return CustomElement;
};
