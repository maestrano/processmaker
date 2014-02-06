function generatedOrder () {
    var orderNow = [];;
    orderNow[0] = [];
    orderNow[1] = [];
    orderNow[2] = [];

    for (var i = 0; i < Ext.getCmp('columnPos0').items.items.length; i++) {
      orderNow[0][i] = Ext.getCmp('columnPos0').items.items[i].dasInsUid;
    }
    for (var i = 0; i < Ext.getCmp('columnPos1').items.items.length; i++) {
      orderNow[1][i] = Ext.getCmp('columnPos1').items.items[i].dasInsUid;
    }
    for (var i = 0; i < Ext.getCmp('columnPos2').items.items.length; i++) {
      orderNow[2][i] = Ext.getCmp('columnPos2').items.items[i].dasInsUid;
    }

    return orderNow;
}

Ext.onReady(function(){

  Ext.state.Manager.setProvider(new Ext.state.CookieProvider());

  // create some portlet tools using built in Ext tool ids
  var tools = [{
    id:'gear',
    handler: function(){
      Ext.Msg.alert('Message', 'The Settings tool was clicked.');
    }
  },{
    id:'close',
    handler: function(e, target, panel){
        panel.ownerCt.remove(panel, true);
    }
  }];

  var tbDashboard = new Ext.Toolbar({
    height: 30,
    items: [
      {
        xtype: 'tbbutton',
        text : _("ID_DASHBOARD_BTNCOLUMNS3"),
        handler : function(a) {
          Ext.MessageBox.show({
            msg: _('ID_LOADING'),
            width:300,
            wait:true,
            waitConfig: {interval:200},
            animEl: 'mb7'
          });

          var vp = Ext.getCmp('viewportDashboard');
          var pd = Ext.getCmp('portalDashboard');

          pd.items.items[0].columnWidth = 0.33;
          pd.items.items[1].columnWidth = 0.33;
          pd.items.items[2].columnWidth = 0.33;
          pd.doLayout();

          tbDashboard.items.items[0].setDisabled(true);
          tbDashboard.items.items[1].setDisabled(false);

          var orderNow = generatedOrder();
          Ext.Ajax.request({
            params: {
              positionCol0: Ext.encode(orderNow[0]),
              positionCol1: Ext.encode(orderNow[1]),
              positionCol2: Ext.encode(orderNow[2]),
              columns: 3
            },
            url: 'dashboard/saveOrderDashlet',
              success: function (res) {
                var data = Ext.decode(res.responseText);
                if (data.success) {
                  Ext.MessageBox.hide();
                }
              },
              failure: function () {
            	  Ext.MessageBox.alert(_('ID_ERROR'), _('ID_IMPORTING_ERROR'));
              }
          });
        }
      },
      {
        xtype: 'tbbutton',
        text : _("ID_DASHBOARD_BTNCOLUMNS2"),
        handler : function(a) {
          Ext.MessageBox.show({
            msg: _('ID_LOADING'),
            progressText: _('ID_SAVING'),
            width:300,
            wait:true,
            waitConfig: {interval:200},
            animEl: 'mb7'
          });

          var vp = Ext.getCmp('viewportDashboard');
          var pd = Ext.getCmp('portalDashboard');

          var dashletMove = new Array();
          for (var i = 0; i < Ext.getCmp('columnPos2').items.items.length; i++) {
            dashletMove.push(Ext.getCmp('columnPos2').items.items[i].id);
          }
          var flag = 0;
          for (var i = 0; i < dashletMove.length; i++) {
            Ext.getCmp('columnPos' + flag).add(Ext.getCmp(dashletMove[i]));
            if (flag == 0) {
              flag = 1;
            } else {
              flag = 0;
            }
          };

          pd.items.items[0].columnWidth = 0.49;
          pd.items.items[1].columnWidth = 0.49;
          pd.items.items[2].columnWidth = 0.01;
          pd.doLayout();

          tbDashboard.items.items[0].setDisabled(false);
          tbDashboard.items.items[1].setDisabled(true);

          var orderNow = generatedOrder();
          Ext.Ajax.request({
            params: {
              positionCol0: Ext.encode(orderNow[0]),
              positionCol1: Ext.encode(orderNow[1]),
              positionCol2: Ext.encode(orderNow[2]),
              columns: 2
            },
            url: 'dashboard/saveOrderDashlet',
              success: function (res) {
                var data = Ext.decode(res.responseText);
                if (data.success) {
                  Ext.MessageBox.hide();
                }
              },
              failure: function () {
                Ext.MessageBox.alert(_('ID_ERROR'), _('ID_IMPORTING_ERROR'));
              }
          });
        }
      }
    ]
  });

  var viewport = new Ext.Viewport({
    layout:'fit',
    name : 'viewportDashboard',
    id   : 'viewportDashboard',
    items:[{
      xtype:'portal',
      region:'center',
      margins:'35 5 5 0',
      tbar: tbDashboard,
      name : 'portalDashboard',
      id   : 'portalDashboard',
      items:[{
        columnWidth:.33,
        id   : 'columnPos0',
        style:'padding:10px 0 10px 10px',
        items:[]
      },{
        columnWidth:.33,
        id   : 'columnPos1',
        style:'padding:10px 0 10px 10px',
        items:[]
      },{
        columnWidth:.33,
        id   : 'columnPos2',
        style:'padding:10px',
        items:[]
      }],
      listeners: {
        'drop': function(e) {
          var orderNow = generatedOrder();
          Ext.MessageBox.show({
            msg: _('ID_LOADING'),
            progressText: _('ID_SAVING'),
            width:300,
            wait:true,
            waitConfig: {interval:200},
            animEl: 'mb7'
          });

          if (tbDashboard.items.items[0].disabled == true) {
            var colum = 3;
          } else {
            var colum = 2;
          }
          Ext.Ajax.request({
            params: {
              positionCol0: Ext.encode(orderNow[0]),
              positionCol1: Ext.encode(orderNow[1]),
              positionCol2: Ext.encode(orderNow[2]),
              columns: colum
            },
            url: 'dashboard/saveOrderDashlet',
              success: function (res) {
                var data = Ext.decode(res.responseText);
                if (data.success) {
                  Ext.MessageBox.hide();
                }
              },
              failure: function () {
                Ext.MessageBox.alert(_('ID_ERROR'), _('ID_IMPORTING_ERROR'));
              }
          });
        }
      }
    }]
  });

  var pd = Ext.getCmp('portalDashboard');
  var con = 0;
  for (var i = 0; i < dashletsInstances.length; i++) {
    for(var d = 0; d < dashletsInstances[i].length; d++) {

      var np = new Ext.ux.Portlet({
        title: dashletsInstances[i][d].DAS_TITLE,
        index: con,
        indicei: i,
        indiced: d,
        dasInsUid : dashletsInstances[i][d].DAS_INS_UID,
        html: 'Gauge Placeholder',
        listeners: {
          'resize': function(p, w, h) {
            var template = new Ext.XTemplate(dashletsInstances[p.indicei][p.indiced].DAS_XTEMPLATE).apply({
              id: p.dasInsUid,
              page: 'dashboard/renderDashletInstance',
              width: w - 12,
              random: Math.floor(Math.random() * 1000000)
            })
            p.update(template);
          }
        }
      });
      pd.items.items[i].add(np);

      con++;
    }
  }


  pd.doLayout();

  if (dashletsColumns == 2) {
    tbDashboard.items.items[0].setDisabled(false);
    tbDashboard.items.items[1].setDisabled(true);

    var pd = Ext.getCmp('portalDashboard');

    pd.items.items[0].columnWidth = 0.49;
    pd.items.items[1].columnWidth = 0.49;
    pd.items.items[2].columnWidth = 0.01;
    pd.doLayout();
  } else {
    var pd = Ext.getCmp('portalDashboard');

    pd.items.items[0].columnWidth = 0.33;
    pd.items.items[1].columnWidth = 0.33;
    pd.items.items[2].columnWidth = 0.33;
    pd.doLayout();

    tbDashboard.items.items[0].setDisabled(true);
    tbDashboard.items.items[1].setDisabled(false);
  }
});