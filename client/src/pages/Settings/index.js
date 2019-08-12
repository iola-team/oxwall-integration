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
  };

  constructor(options) {
    this.container = $('#' + options.uniqId);
    this.logoInput = this.ref('logoInput');
    this.backgroundInput = this.ref('backgroundInput');
    this.primaryColorPicker = this.ref('primaryColorPicker');
    this.primaryColorInput = this.ref('primaryColorInput');
    this.preview = this.ref('preview')
    this.saveButton = this.ref('saveButton');

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

    this.saveButton.click(() => (this.onSave(), false));
  }

  ref(name) {
    return $(`[data-ref="${name}"]`, this.container);
  }

  setState(state) {
    this.state = { ...this.state, ...state };

    requestAnimationFrame(() => this.render());
  }

  onSave() {
    console.log(this.state);
  }

  render() {
    const { primaryColor, backgroundUrl, logoUrl } = this.state;

    this.primaryColorInput.val(primaryColor?.substring(1));
    this.primaryColorPicker.attr('value', primaryColor);
    this.preview.attr('background', backgroundUrl);
    this.preview.attr('logo', backgroundUrl);
  }
}

export default {
  init(options) {
    return new Settings(options);
  }
}
