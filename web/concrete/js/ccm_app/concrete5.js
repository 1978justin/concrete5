var c5 = (function(ccm_event) {
  "use strict";

  function getAttribute(attributes, key) {
    return attributes[key];
  }
  function setAttribute(attributes, key, value) {
    return attributes[key] = value;
  }

  var concrete5 = {
    editMode: null,
    event: ccm_event,

    /**
     * Create the getter / setter methods and attach them if they don't exist
     * @param  {Object} attributes Object containing the attributes to create getter/setters for.
     * @return {Boolean}           Success, always true.
     */
    createGetterSetters: function generateGetterSetters(attributes) {
      var obj = this;
      obj.getAttr = _.partial(getAttribute, attributes);
      obj.setAttr = _.partial(setAttribute, attributes);
      _(attributes).each(function(value, key){

        key += ""; // Make sure we always have a string.
        var get_method = "get" + key.substr(0, 1).toUpperCase() + key.substr(1),
            set_method = "set" + key.substr(0, 1).toUpperCase() + key.substr(1),
            defaults = {};

        if (typeof obj[get_method] == 'undefined') {
          obj[get_method] = _.partial(getAttribute, attributes, key);
        }
        if (typeof obj[set_method] == 'undefined') {
          obj[set_method] = _.partial(setAttribute, attributes, key);
        }
      });
      return true;
    }
  };

  return concrete5;

}(ccm_event));
