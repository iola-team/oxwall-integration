/*
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

import $ from 'jquery';

class Settings {
  container = null;
  logoInput = null;
  backgroundInput = null;
  primaryColorInput = null;

  state = {
    primaryColor: null,
    backgroundUrl: null,
    backgroundFile: null,
    logoUrl: null,
    logoFile: null,
    inProgress: false,
  };

  constructor(options) {
    this.container = $('#' + options.uniqId);
    this.logoInput = this.ref('logoInput');
    this.backgroundInput = this.ref('backgroundInput');
    this.primaryColorPicker = this.ref('primaryColorPicker');
    this.primaryColorInput = this.ref('primaryColorInput');
    this.preview = this.ref('preview')
    this.browseBackground = this.ref('browseBackground')
    this.browseLogo = this.ref('browseLogo')
    this.saveButton = this.ref('saveButton');
    this.saveResponderUrl = options.rsp.save;

    this.setState(options.values || {});

    // Event listeners

    this.primaryColorPicker.change(({ target }) => this.setState({
      primaryColor: target.getColor(),
    }))

    this.primaryColorInput.on('input', ({ target }) => this.setState({
      primaryColor: target.value && '#' + target.value,
    }));

    this.backgroundInput.change(({ originalEvent: { detail: { file, url } } }) => this.setState({
      backgroundUrl: url, backgroundFile: file,
    }));

    this.logoInput.change(({ originalEvent: { detail: { file, url } } }) => this.setState({
      logoUrl: url, logoFile: file,
    }));

    this.saveButton.click(e => (this.onSave(e), false));
    this.browseLogo.click(e => (this.onBrowseLogoPress(e), false));
    this.browseBackground.click(e => (this.onBrowseBackgroundPress(e), false));
  }

  ref(name) {
    return $(`[data-ref="${name}"]`, this.container);
  }

  setState(state) {
    this.state = { ...this.state, ...state };

    requestAnimationFrame(() => this.render());
  }

  async onSave() {
    const { primaryColor, backgroundFile, logoFile } = this.state;
    const formData = new FormData();

    if (primaryColor) {
      formData.append('primaryColor', primaryColor);
    }

    if (backgroundFile) {
      formData.append('background', backgroundFile)
    }
    
    if (logoFile) {
      formData.append('logo', logoFile)
    }

    this.setState({ inProgress: true });
    const response = await fetch(this.saveResponderUrl, {
      method: 'post',
      body: formData,
    })

    const data = await response.json();
   
    data.info && OW.info(data.info);
    data.error && OW.error(data.error);

    this.setState({ inProgress: false });
  }

  onBrowseLogoPress() {
    this.logoInput.get(0).open();
  }

  onBrowseBackgroundPress() {
    this.backgroundInput.get(0).open();
  }

  render() {
    const { primaryColor, backgroundUrl, logoUrl, inProgress } = this.state;

    this.primaryColorInput.val(primaryColor?.substring(1));
    this.primaryColorPicker.attr('value', primaryColor);
    this.preview.attr('background', backgroundUrl);
    this.preview.attr('logo', logoUrl);
    this.preview.attr('primary-color', primaryColor);

    this.saveButton.get(0).disabled = inProgress;
    this.saveButton[inProgress ? 'addClass': 'removeClass']('ow_inprogress');
  }
}

export default {
  init(options) {
    return new Settings(options);
  }
}
