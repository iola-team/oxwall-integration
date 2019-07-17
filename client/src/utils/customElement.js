import React from 'react';
import ReactDom from 'react-dom';
import camelCase from 'camelcase';

const extractAttrs = (attrs, element) => attrs.reduce((props, attrName) => {
  props[camelCase(attrName)] = element.getAttribute(attrName);

  return props;
}, {});

export default (options = {}) => Component => {
  const observedAttributes = options.attrs || [];
  const shadowRoots = new WeakMap();
  const componentInstances = new WeakMap();

  const render = element => ReactDom.render(
    React.createElement(Component, extractAttrs(observedAttributes, element)),
    shadowRoots.get(element),
    function () {
      componentInstances.set(element, this);
    },
  );

  class CustomElement extends HTMLElement {
    static observedAttributes = observedAttributes;

    constructor(...args) {
      super(...args);

      shadowRoots.set(this, this.attachShadow({ mode: 'open' }));
    }

    connectedCallback() {
      render(this);
    }

    attributeChangedCallback() {
      render(this);
    }

    disconnectedCallback() {
      ReactDom.unmountComponentAtNode(shadowRoots.get(this));
    }
  }

  if (options.methods) {
    Object.assign(CustomElement.prototype, options.methods.reduce((methods, methodName) => ({
      ...methods,
      [methodName]: function (...args) {
        return componentInstances.get(this)?.[methodName] ?.(...args);
      },
    }), {}));
  }

  if (options.tag) {
    customElements.define(options.tag, CustomElement);
  }

  return CustomElement;
};
