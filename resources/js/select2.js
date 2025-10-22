$('.select2').select2();
$('.time_range').daterangepicker({
    timePicker: true,
    timePickerIncrement: 30,
    timePicker24Hour: true,
    locale: {
        format: 'MM/DD/YYYY HH:mm'
    }
});
$.fn.indexSelector = function () {
    var $selectors = $(this),
        limit = 300;
    $selectors.each(function () {
        var $select = $(this),
            ids = $select.data("ids") || '',
            itemGetUrl = '/admin/cms/defi/index/selector';
        $select.select2({
            ajax: {
                url: function () {
                    return itemGetUrl;
                },
                dataType: 'json',
                delay: 50,
                data: function (params) {
                    return {
                        keyword: params.term, // search term
                        limit: limit,
                        chain: $("#chain").val(),
                    };
                },
                processResults: function (data, params) {
                    console.log("processResults");
                    var results = [];

                    // parse the results into the format expected by Select2
                    // since we are using custom formatting functions we do not need to
                    // alter the remote JSON data, except to indicate that infinite
                    // scrolling can be used
                    params.page = params.page || 1;

                    for (var i = 0, l = data.data.length, item; i < l; i++) {
                        item = data.data[i];
                        results.push(item);
                    }
                    return {
                        results: results
                    };
                },
                cache: true
            },
            escapeMarkup: function (markup) {
                return markup;
            }, // let our custom formatter work
            minimumInputLength: 0,
            templateResult: function (repo) {
                if (repo.loading) return repo.text;

                var markup = "<div class='select2-result-repository clearfix'>" +
                    "<div class='select2-result-repository__meta' style='float:left;width:90%;'>" +
                    "<div class='select2-result-repository__title'>" + repo.title + "</div>" +
                    "</div>" +
                    "</div>";

                return markup;
            },
            templateSelection: function (repo) {
                return repo.title || repo.text;
            }
        });
        console.log(ids);
        if (ids) {
            var chain = $('#chain').val();
            $.get(itemGetUrl, {ids, chain}, function (res) {
                var optionData = [],
                    optionHtml = "";
                $.each(res.data || [], function (i, n) {
                    n.name = n.title;
                    optionData.push({id: n.id, title: n.name});
                    optionHtml += '<option value="' + n.id + '" title="' + n.name + '" selected>' + n.name + '</option>';
                });
                $select.html(optionHtml).data("select2").trigger("selection:update", {data: optionData});
            });
        }

    });
};
$('.index-selector').indexSelector();
