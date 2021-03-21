jQuery(($) => {
    $('#Ranks tbody').sortable({
        axis: 'y',
        containment: 'parent',
        cursor: 'move',
        cursorAt: {
            left: '10px'
        },
        forcePlaceholderSize: true,
        items: 'tr',
        placeholder: 'Placeholder',
        opacity: .6,
        tolerance: 'pointer',
        update() {
            // Save the current sort method
            $.post(
                gdn.url('rank/sort.json'), {
                    'SortArray': $('#Ranks tbody').sortable('toArray'),
                    'TransientKey': gdn.definition('TransientKey')
                },
                (response) => {
                    if (!response || !response.Result) {
                        alert("Oops - Didn't save order properly");
                    }
                }
            );
        },
        helper(e, ui) {
            // Preserve width of row
            ui.children().each(function () {
                $(this).width($(this).width());
            });
            return ui;
        }
    });
});
