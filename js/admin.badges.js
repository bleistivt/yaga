jQuery(($) => {
  // Poor mans cache
  const Cache = {
    data: {},
    remove(key) {
      delete Cache.data[key];
    },
    exists(key) {
      return Cache.data.hasOwnProperty(key) && Cache.data[key] !== null;
    },
    get(key) {
      return Cache.data[key];
    },
    set(key, cachedData) {
      Cache.remove(key);
      Cache.data[key] = cachedData;
    },
  };

  $("#Badges tbody").sortable({
    axis: "y",
    containment: "parent",
    cursor: "move",
    cursorAt: {
      left: "10px",
    },
    forcePlaceholderSize: true,
    items: "tr",
    placeholder: "Placeholder",
    opacity: 0.6,
    tolerance: "pointer",
    update() {
      $.post(
        gdn.url("badge/sort.json"),
        {
          SortArray: $("#Badges tbody").sortable("toArray"),
          TransientKey: gdn.definition("TransientKey"),
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
    },
  });

  // Store the current inputs in the form
  $(document).on("blur", "#Rule-Criteria input", function () {
    $(this).attr("value", $(this).val());
  });
  $(document).on("blur", "#Rule-Criteria select", function () {
    const currentValue = $(this).val();
    $(this)
      .children("option")
      .each(function () {
        $(this).removeAttr("selected");
      });
    $(this)
      .find("option[value='" + currentValue + "']")
      .attr("selected", "selected")
      .prop("selected", true);
  });

  // This handles retrieving and displaying the different rule criteria forms
  $("form.Badge select[name='RuleClass']")
    .focus(function () {
      // Save the current form to the current value's cache on focus
      const Rule = $(this).val();
      const RuleForm = $("#Rule-Criteria").html();
      const RuleDesc = $("#Rule-Description").html();
      Cache.set(Rule, {
        Form: RuleForm,
        Description: RuleDesc,
      });
    })
    .change(function () {
      // Grab the form from cache or ajax on change
      const NewRule = $(this).val();
      if (Cache.exists(NewRule)) {
        $("#Rule-Criteria").fadeOut(function () {
          $(this).html(Cache.get(NewRule).Form).fadeIn();
        });
        $("#Rule-Description").fadeOut(function () {
          $(this).html(Cache.get(NewRule).Description).fadeIn();
        });
      } else {
        // Grab the form via ajax
        const url = gdn.url("/badge/rulecriteriaform/" + NewRule);
        $.ajax({
          url,
          global: false,
          type: "GET",
          data: {
            DeliveryMethod: "JSON",
          },
          dataType: "json",
          success({ CriteriaForm, Description }) {
            Cache.set(NewRule, {
              Form: CriteriaForm,
              Description: Description,
            });
            $("#Rule-Criteria").fadeOut(function () {
              $(this).html(Cache.get(NewRule).Form).fadeIn();
            });
            $("#Rule-Description").fadeOut(function () {
              $(this).html(Cache.get(NewRule).Description).fadeIn();
            });
          },
          error(jqXHR) {
            gdn.informError(jqXHR);
          },
        });
      }
    });
});
