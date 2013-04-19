im.bind('imageload',function(){
  var cs = settings.controlsets || {}, filters = settings.filters || {}, namespace, firstcs;
  var running = 0;
  log('Loading ControlSets');
  im.showLoader('Loading Control Sets..');
  im.fire('LoadingControlSets');
  for (namespace in cs) {
    var myns = "ControlSet_" + namespace;
    $.ajax(cs[namespace]['src'],{
      dataType:'text',
      cache:false,
      namespace:namespace,
      myns:myns,
      beforeSend:function(){running++;},
      success:function(js){
        running--;
        var nso = im.addControlSet(this.myns,js,cs[this.namespace]['element']);
        log(nso);
        im.fire('controlSetLoad',nso);
        if (0 == running) {
          im.trigger('ControlSetsLoaded');
        }
      },
      error: function(xhr, errDesc, exception) {
        running--;
        if (0 == running) {
          im.trigger('ControlSetsLoaded');
        }
      }
    });
  }
});
im.adjustSavers = function() {
  if (im.activeElement.elementType != "stage" && im.autoCrop) {
    im.alterCore('saveWidth',Math.ceil(-(im.activeElement.getX() - im.center.x)*2));
    im.alterCore('saveHeight',Math.ceil(-(im.activeElement.getY() - im.center.y)*2));
    if ((im.activeElement.getWidth() - im.saveWidth / 2) * 2 > im.saveWidth) {
      im.alterCore('saveWidth', Math.ceil((im.activeElement.getWidth() - im.saveWidth / 2) * 2));
    }
    if ((im.activeElement.getHeight() - im.saveHeight / 2) * 2 > im.saveHeight) {
      im.alterCore('saveHeight', Math.ceil((im.activeElement.getHeight() - im.saveHeight / 2) * 2));
    }
    im.buildBackground();
    im.fire('saveSizeChange');
  }
};
im.bind('ControlSetsLoaded',function(){
  im.fire('LoadingComponents');
  im.showLoader('Loading Components..');
  var components = settings.components || {}, namespace, running = 0;
  log('Loading Components');
  for (namespace in components) {
    var myns = "Component_" + namespace;
    $.ajax(components[namespace]['src'],{
      dataType:'text',
      cache:false,
      namespace:namespace,
      myns:myns,
      beforeSend:function(){running++;},
      success:function(js){
        running--;
        var nso = im.addComponent(this.myns,js,components[this.namespace]['element']);
        log(nso);
        im.fire('ComponentLoad',nso);
        if (0 == running) {
          im.trigger('ComponentsLoaded');
        }
      },
      error: function(xhr, errDesc, exception) {
        running--;
        if (0 == running) {
          im.trigger('ComponentsLoaded');
        }
      }
    });
  }
});

im.bind('ComponentsLoaded',function(){ // do this when the control sets finish loading.
  log('Loading Filters');
  im.showLoader('Loading Filters..');
  var filters = settings.filters || {}, namespace, firstf, firstc, active = 0;
  im.fire('LoadingFilters');
  for (namespace in filters) {
    var myns = "Filter_" + namespace;
    var name = filters[namespace].name;
    if (!firstf) firstf = myns;
    active++;
    $.ajax(filters[namespace].src,{
      dataType:'text',
      cache:false,
      namespace:namespace,
      myns:myns,
      name:name,
      success:function(js){
        var nso = im.addFilter(this.myns,js);
        nso.name = this.name;
        im.fire('filterLoad',nso);
        active--;
        if (0 == active) {
          im.trigger('FiltersLoaded');
        }
      },
      error: function(xhr, errDesc, exception) {
        active--;
        if (0 == active) {
          im.trigger('FiltersLoaded');
        }
      }
    });
  }
});
im.bind('ChangeActiveAction',function(e){
  var ns = e.eventData;
  if (ns === im.activeControlSet) return;
  for (var ons in im.controlSets) {
    getElem(im.controlSets[ons]);
    if (ons !== ns) getElem(im.controlSets[ons]).slideUp();
  }
  im.activeControlSet = ns;
  im.alterCore('activeControlSet',ns);
  if (!ns) {
    $('div.control-sets',im.controlContext).find('h4.active').removeClass('active');
    return;
  }
  var cs = $(im.controlSets[ns]),
      height = cs.show().height();
  if (cs.length == 0) return;
  cs.hide().height(height).slideDown(function(){$(this).height('')});
});

im.bind('ChangeActiveComponent',function(e){
  var ns = e.eventData;
  if (ns === im.activeComponent) return;
  for (var ons in im.components) {
    if (ons !== ns) getElem(im.components[ons]).slideUp();
  }
  im.activeComponent = ns;
  im.alterCore('activeComponent',ns);
  if (!ns) return;
  var cs = $(im.components[ns]),
      height = cs.show().height();
  if (cs.length == 0) return;
  cs.hide().height(height).slideDown(function(){$(this).height('')});
});

im.bind('ChangeNavTab',function(e) {
  log('changenavtab',e);
  im.trigger('ChangeActiveAction',e.eventData);
  im.trigger('ChangeActiveComponent',e.eventData);
  var parent = getElem('div.editorcontrols');
  switch(e.eventData) {
    case 'add':
      parent.children('div.control-sets').hide();
      parent.children('div.components').show();
      break;
    case 'edit':
      parent.children('div.components').hide();
      parent.children('div.control-sets').show();
      break;
  }
});


im.bind('FiltersLoaded',function(){
  im.hideLoader();
});