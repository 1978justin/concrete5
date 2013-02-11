im.extend = function(property,value) {
  im[property] = value;
};

im.alterCore = function(property,value) {
  var im = im, ns = 'core', i;
  if (im.namespace) {
    var ns = im.namespace;
    im = window.c5_image_editor;
  }
  im[property] = value;
  for (i in im.namespaces){
    im.namespaces[i][property] = value;
  }
};

im.clone = function(namespace) {
  var newim = new ImageEditor(),i;
  for (i in im) {
    newim[i] = im[i];
  }
  newim.namespace = namespace;
  im.namespaces['namespace'] = newim;
  return newim;
};


im.addControlSet = function(ns,js,elem) {
  if (jQuery && elem instanceof jQuery) elem = elem[0];
  elem.controlSet = function(im,js) {
    this.im = im;
    eval(js);
    return this;
  };
  var newim = im.clone(ns);
  var nso = elem.controlSet(newim,js);
  im.controlSets[ns] = nso;
  return nso;
};

im.addFilter = function(ns,js) {
  var filter = function(im,js) {
    this.im = im;
    eval(js);
    return this;
  };
  var newim = im.clone(ns);
  var nso = new filter(newim,js);
  im.filters[ns] = nso;
  return nso;
};

im.addComponent = function(ns,js) {
  var component = function(im,js) {
    this.im = im;
    eval(js);
    return this;
  };
  var newim = im.clone(ns);
  var nso = new component(newim,js);
  im.components[ns] = nso;
  return nso;
};