jQuery(window).load(function(){jQuery(".fusion-toggle-boxed-mode .panel-collapse").on("click",function(i){jQuery(i.target).is("a")||jQuery(i.target).is("button")||jQuery(i.target).hasClass("fusion-button-text")||jQuery(this).parents(".fusion-panel").find(".panel-title > a").trigger("click")}),window.fusionAccordianClick=!1,jQuery(document).on("click dblclick",".fusion-accordian .panel-title a",function(i){var e,n,o;i.preventDefault(),jQuery(this).parents(".fusion-accordian").find(".toggle-fadein").length&&jQuery(this).parents(".fusion-accordian").find(".toggle-fadein")[0]!==jQuery(this).parents(".fusion-panel").find(".panel-collapse")[0]||!0!==window.fusionAccordianClick&&(window.fusionAccordianClick=!0,e=jQuery(this),n=jQuery(jQuery(this).data("target")).find(".panel-body"),o=e.parents(".fusion-accordian").find(".panel-title a"),e.hasClass("collapsed")?(void 0!==e.data("parent")?o.removeClass("active"):e.removeClass("active"),e.closest(".fusion-fullwidth").hasClass("fusion-equal-height-columns")&&setTimeout(function(){jQuery(window).trigger("fusion-resize-horizontal")},350)):(void 0!==e.data("parent")&&o.removeClass("active"),e.addClass("active"),setTimeout(function(){"function"==typeof jQuery.fn.reinitializeGoogleMap&&n.find(".shortcode-map").each(function(){jQuery(this).reinitializeGoogleMap()}),n.find(".fusion-carousel").length&&"function"==typeof generateCarousel&&generateCarousel(),n.find(".fusion-portfolio").each(function(){var i=jQuery(this).find(".fusion-portfolio-wrapper"),e=i.attr("id");e&&(i=jQuery("#"+e)),i.isotope()}),n.find(".fusion-gallery").each(function(){jQuery(this).isotope()}),"function"==typeof jQuery.fn.fusionCalcFlipBoxesHeight&&n.find(".fusion-flip-boxes").not(".equal-heights").find(".flip-box-inner-wrapper").each(function(){jQuery(this).fusionCalcFlipBoxesHeight()}),"function"==typeof jQuery.fn.fusionCalcFlipBoxesEqualHeights&&n.find(".fusion-flip-boxes.equal-heights").each(function(){jQuery(this).fusionCalcFlipBoxesEqualHeights()}),"function"==typeof jQuery.fn.equalHeights&&n.find(".fusion-fullwidth.fusion-equal-height-columns").each(function(){jQuery(this).find(".fusion-layout-column .fusion-column-wrapper").equalHeights()}),n.find(".crossfade-images").each(function(){fusionResizeCrossfadeImagesContainer(jQuery(this)),fusionResizeCrossfadeImages(jQuery(this))}),n.find(".fusion-blog-shortcode").each(function(){jQuery(this).find(".fusion-blog-layout-grid").isotope()}),n.find(".fusion-testimonials .reviews").each(function(){jQuery(this).css("height",jQuery(this).children(".active-testimonial").height())}),"function"==typeof calcSelectArrowDimensions&&calcSelectArrowDimensions(),"function"==typeof wrapGravitySelects&&wrapGravitySelects(),jQuery(window).trigger("fusion-resize-horizontal")},350)),window.fusionAccordianClick=!1)})}),jQuery(document).ready(function(){jQuery(".fusion-accordian .panel-title a").click(function(i){i.preventDefault()})});