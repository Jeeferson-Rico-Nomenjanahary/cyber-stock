;(function(window) {
    'use strict';
    var self;
    function Ux() {
        self = this;
        self._init();
    };
    Ux.prototype = {
        _init: function() {
            window.onload = function() {
                self._initDatepicker();
                self._initSelectize();
            }
        },
        _initSelectize: function() {
            $('select:not([class*="no-selectize"]):not([class*="multiple"]):not([class*="input-tags-calcul"])').selectize();
            $('select.multiple').selectize({
                plugins: ['remove_button'],
                maxItems: null,
            });
        },

        _initDatepicker: function() {
            $('.datepicker').datetimepicker({
                locale: 'fr',
                format: 'DD/MM/YYYY',
                //debug:true,
                icons: {
                    previous: 'glyphicon glyphicon-menu-left',
                    next: 'glyphicon glyphicon-menu-right'
                }
            });
            $(function () {
                $('.date-from').datetimepicker();
                $('.date-to').datetimepicker({
                    useCurrent: false //Important! See issue #1075
                });
                $(".date-from").on("dp.change", function (e) {
                    $('.date-to').data("DateTimePicker").minDate(e.date);
                });
                $(".date-to").on("dp.change", function (e) {
                    $('.date-from').data("DateTimePicker").maxDate(e.date);
                });
            });
        }
    }
    window.Ux = Ux;

})(window);