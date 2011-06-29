/**
 * PanelItem: Clock
 *
 * @package OSjs.Panel
 * @author Anders Evenrud <andersevenrud@gmail.com>
 * @class
 */
var PanelItemClock = (function($, undefined) {
  return function(_PanelItem, panel, api, argv) {

    var _PanelItemClock = _PanelItem.extend({
      init : function() {
        this._super("PanelItemClock", "right");
        this._named = "Clock";
      },

      create : function(pos) {
        var ret = this._super(pos);
        $(ret).append("<span></span>");

        var d = new Date();
        $(ret).find("span").html(sprintf("%02d/%02d/%02d %02d:%02s", d.getDate(), d.getMonth(), d.getYear(), d.getHours(), d.getMinutes()));

        // Start clock
        this.clock_interval = setInterval(function() {
          var d = new Date();
          $(ret).find("span").html(sprintf("%02d/%02d/%02d %02d:%02s", d.getDate(), d.getMonth(), d.getYear(), d.getHours(), d.getMinutes()));
        }, 500);

        return ret;
      },


      destroy : function() {
        if ( this.clock_interval ) {
          clearInterval(this.clock_interval);
        }

        this._super();
      }
    });

    return construct(_PanelItemClock, argv);
  };
})($);