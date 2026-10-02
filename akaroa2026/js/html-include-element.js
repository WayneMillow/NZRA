const LINK_LOAD_SUPPORTED = 'onload' in HTMLLinkElement.prototype;

/**
 * Firefox may throw an error when accessing a not-yet-loaded cssRules property.
 * @param {HTMLLinkElement}
 * @return {boolean}
 */
function isLinkAlreadyLoaded(link) {
  try {
    return !!(link.sheet && link.sheet.cssRules);
  } catch (error) {
    if (error.name === 'InvalidAccessError' || error.name === 'SecurityError')
      return false;
    else
      throw error;
  }
}

/**
 * Resolves when a `<link>` element has loaded its resource.
 * Gracefully degrades for browsers that don't support the `load` event on links.
 * in which case, it immediately resolves, causing a FOUC, but displaying content.
 * resolves immediately if the stylesheet has already been loaded.
 * @param  {HTMLLinkElement} link
 * @return {Promise<StyleSheet>}
 */
async function linkLoaded(link) {
  return new Promise((resolve, reject) => {
    if (!LINK_LOAD_SUPPORTED) resolve();
    else if (isLinkAlreadyLoaded(link)) resolve(link.sheet);
    else {
      link.addEventListener('load', () => resolve(link.sheet), { once: true });
      link.addEventListener('error', reject, { once: true });
    }
  });
}

/**
 * Embeds HTML into a document.
 *
 * The HTML is fetched from the URL contained in the `src` attribute, using the
 * fetch() API. A 'load' event is fired when the HTML is updated.
 *
 * The request is made using CORS by default. This can be chaned with the `mode`
 * attribute.
 *
 * By default, the HTML is embedded into a shadow root. If the `no-shadow`
 * attribute is present, the HTML will be embedded into the child content.
 *
 */
class HTMLIncludeElement extends HTMLElement {
  static get observedAttributes() {
    return ['src', 'page'];
  }

  /**
   * The URL to fetch an HTML document from.
   *
   * Setting this property causes a fetch the HTML from the URL.
   */
  get src() {
    return this.getAttribute('src');
  }

  set src(value) {
    this.setAttribute('src', value);
  }

  get page() {
    return this.getAttribute('page');
  }

  set page(value) {
    this.setAttribute('page', value);
  }

  constructor() {
    super();
  }

  async attributeChangedCallback(name, oldValue, newValue) {
    if (name == 'src') {
      let text = '';
      try {
        const response = await fetch(newValue);
        if (!response.ok) {
          throw new Error(`html-include fetch failed: ${response.statusText}`);
        }
        text = await response.text();
        if (this.src !== newValue) {
          // the src attribute was changed before we got the response, so bail
          return;
        }
      } catch(e) {
        console.error(e);
      }
      $(text).insertBefore(this)
      this.remove();
    }
    
    if (name == 'page') {
      var activeLink = $('a:contains("' + newValue + '")');
      activeLink.parent().addClass('active');
    }

    this.dispatchEvent(new Event('load'));
    $(document).foundation();
  }
}
customElements.define('html-include', HTMLIncludeElement);