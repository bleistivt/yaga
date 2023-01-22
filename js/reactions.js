jQuery(($) => {
  $(".Expander").expander({
    slicePoint: 200,
    expandText: gdn.definition("ExpandText"),
    userCollapseText: gdn.definition("CollapseText"),
  });
});
