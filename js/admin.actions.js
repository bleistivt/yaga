jQuery(($) => {
    $('#Actions tbody').sortable({
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
            $.post(
                gdn.url('action/sort.json'), {
                    'SortArray': $('#Actions tbody').sortable('toArray'),
                    'TransientKey': gdn.definition('TransientKey')
                },
                (response) => {
                    if (!response || !response.Result) {
                        alert("Oops - Didn't save order properly");
                    }
                }
            );
        }
    });

    const formSetup = () => {
        // If someone types in the class manually, deselect icons and select if needed
        $("input[name='CssClass']").on('input', function () {
            $('#ActionIcons img.Selected').removeClass('Selected');

            const FindCssClass = $(this).val();
            if (FindCssClass.length) {
                $("#ActionIcons img[data-class='" + FindCssClass + "']").addClass('Selected');
            }
        });

        $('#ActionIcons img').click(function () {
            const newCssClass = $(this).data('class');
            $("input[name='CssClass']").val(newCssClass);
            $('#ActionIcons img.Selected').removeClass('Selected');
            $(this).addClass('Selected');
        });

        const DeleteForm = $("form#DeleteAction");
        const OtherAction = DeleteForm.find('#ReplacementAction');
        OtherAction.css('opacity', '0.5');
        gdn.disable(OtherAction);

        // Toggle the display of the dropdown with the checkbox
        DeleteForm.find('#MoveAction').change(function () {
            if ($(this).is(':checked')) {
                OtherAction.css('opacity', '1');
                gdn.enable(OtherAction);
            } else {
                OtherAction.css('opacity', '0.5');
                gdn.disable(OtherAction);
            }
        });
    };

    formSetup();

    // Wait to hide things after a popup reveal has happened
    $(document).on('contentLoad', formSetup);
});
