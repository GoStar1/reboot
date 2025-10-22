require('./bootstrap');

require('../../node_modules/admin-lte/plugins/jquery/jquery.min.js');
window.$ = window.jQuery = require('jquery');
window.BigNumber = require('../../node_modules/bignumber.js');
window.moment = (require('../../node_modules/moment/dist/moment.js')).default;
// window.moment = require('../../node_modules/admin-lte/plugins/daterangepicker/moment.min.js').default;
window.moment = require('../../node_modules/moment-timezone/index');
require('../../node_modules/admin-lte/plugins/daterangepicker/daterangepicker.js');
require('../../node_modules/admin-lte/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js');
require('../../node_modules/admin-lte/plugins/bootstrap/js/bootstrap.bundle.min.js');
window.toastr = require('../../node_modules/admin-lte/plugins/toastr/toastr.min.js');
require('../../node_modules/admin-lte/plugins/chart.js/Chart.min.js');
require('../../node_modules/admin-lte/plugins/select2/js/select2.js');
require('../../node_modules/admin-lte/dist/js/adminlte.js');
require('../../node_modules/jquery-form/src/jquery.form.js');
$.fn.serializeObject = function () {
    var o = {};
    var a = this.serializeArray();
    $.each(a, function () {
        if (o[this.name]) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });
    return o;
};
