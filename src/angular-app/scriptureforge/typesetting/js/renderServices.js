'use strict';

angular.module('typesetting.renderServices', ['jsonRpc'])
  .service('typesettingRenderService', ['jsonRpc',
  function(jsonRpc) {
    jsonRpc.connect('/api/sf');

    this.getPageDto = function getPageDto(callback) {
      jsonRpc.call('typesetting_renderPage_dto', [], callback);
    };

    this.doRender = function doRender(callback) {
      jsonRpc.call('typesetting_render_doRender', [], callback);
    };

  },])

  ;
