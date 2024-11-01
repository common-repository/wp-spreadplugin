/**
 * Plugin Name: WP-Spreadplugin
 * Plugin URI: https://wordpress.org/plugins/wp-spreadplugin/
 * Description: This plugin uses the Spreadshirt API to list articles and let your customers order articles of your Spreadshirt shop using Spreadshirt order process.
 * Version: 4.8.2
 * Author: Thimo Grauerholz
 */
/* globals ajax_object,spreadshirt */

function getParameterByName(name) {
  const newname = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
  const regexS = `[\\?&]${newname}=([^&#]*)`;
  const regex = new RegExp(regexS);
  const results = regex.exec(window.location.search);
  if (results == null) {
    return "";
  }
  return encodeURIComponent(decodeURIComponent(results[1].replace(/\+/g, " ")));
}

let sep = "?";
let sor = getParameterByName("articleSortBy");
const paged = getParameterByName("pagesp");
const infiniteItemSel = ".spreadplugin-article";
let appearance = "";
let view = "";

if (ajax_object.pageLink.indexOf("?") > -1) {
  sep = "&";
}

jQuery(($) => {
  function refreshCart(json) {
    $(".spreadplugin-checkout-link").attr("href", json.c.u);
    $(".spreadplugin-checkout-link").removeAttr("title");
    $(".spreadplugin-checkout span").text(json.c.q);
    $(".spreadplugin-cart-checkout a").attr("href", json.c.u);

    // &'+sid
    $.get(ajax_object.ajaxLocation, "action=myCart", (data) => {
      $(".spreadplugin-cart").html(data);

      // checkout in an iframe in page
      if (ajax_object.pageCheckoutUseIframe == 1) {
        $(".spreadplugin-cart-checkout > a").click((event) => {
          event.preventDefault();

          const checkoutLink = $(event.currentTarget).attr("href");

          if (typeof checkoutLink !== "undefined" && checkoutLink.length > 0) {
            $("#spreadplugin-items #pagination").remove();
            $("#spreadplugin-items #spreadplugin-menu").remove();
            $(window).unbind(".infscr");

            $("#spreadplugin-list").html(
              '<iframe style="z-index:10002" id="checkoutFrame" frameborder="0" width="900" height="2000" scroll="yes">'
            );
            $("#spreadplugin-list #checkoutFrame").attr("src", checkoutLink);

            $("html, body").animate(
              {
                scrollTop: $("#spreadplugin-items #checkoutFrame").offset().top,
              },
              2000
            );
          }
        });
      }

      $(".cart-row a.deleteCartItem").click((e) => {
        e.preventDefault();
        $(e.currentTarget).closest(".cart-row").show().fadeOut("slow");

        // &'+sid+'
        $.post(
          ajax_object.ajaxLocation,
          `action=myDelete&id=${$(e.currentTarget)
            .closest(".cart-row")
            .data("id")}`,
          () => {
            $.post(
              ajax_object.ajaxLocation,
              "action=myAjax",
              (res) => {
                refreshCart(res);
              },
              "json"
            );
          }
        );
      });

      // hide cart when user clicks close
      $(".spreadplugin-cart-close").click((e) => {
        e.preventDefault();
        $(".spreadplugin-cart").hide();
      });
    });
  }

  // integrated designer shop // conformat
  function callIntegratedDesigner(
    desiredDesignId,
    desiredProducttypeId,
    desiredAppearanceId,
    desiredViewId
  ) {
    // @see https://spreadshirt.github.io/apps/sketchomat
    spreadshirt.create(
      "sketchomat",
      {
        shopId: ajax_object.designerShopId,
        target: document.getElementById(ajax_object.designerTargetId),
        platform: ajax_object.designerPlatform,
        locale: ajax_object.designerLocale,
        width: ajax_object.designerWidth,
        // productId: desiredProductId,
        designId: desiredDesignId,
        appearanceId: desiredAppearanceId,
        productTypeId: desiredProducttypeId,
        viewId: desiredViewId,
        cssUrl: ajax_object.cssSketchomatLocation,
        /*
      * Currently disabled - apiBasketId not taken?

      apiBasketId: ajax_object.designerBasketId,
      basketId: ajax_object.designerBasketId,

      addToBasket: function(item, callback) {

         // implement how to get the item to your basket
         // e.g. do some AJAX request

         // invoke callback function when you're done

         var err = null; // set to a js truly type for showing an error in tabloat,
         // see http://www.sitepoint.com/javascript-truthy-falsy/

         callback && callback(err);

      }
      */
        addToBasket(basketItem, callback) {
          const data = {
            article: basketItem.product.id,
            size: basketItem.size.id,
            appearance: basketItem.appearance.id,
            quantity: basketItem.quantity,
            shopId: basketItem.shopId,
            action: "myAjax",
            type: "1", // type switch for using articleId as productId
          };

          $.post(
            ajax_object.ajaxLocation,
            data,
            (json) => {
              if (json.c.m == 1) {
                // return success to confomat
                callback && callback();
              } else {
                // return failure to confomat
                callback && callback(true);
              }

              // Refresh shopping cart
              refreshCart(json);
            },
            "json"
          );
        },
      },
      (err, app) => {
        if (err) {
          // something went wrong
          // console.log(err);
        } else {
          // cool I can control the application (see below)
          // app.setProductTypeId(6);
        }
      }
    );
  }

  /*
   * change article color and view
   */
  function bindClick() {
    // avoid double firing events
    $(
      ".spreadplugin-article .colors li,.spreadplugin-article-detail .colors li"
    ).unbind();
    $(
      ".spreadplugin-article .views li,.spreadplugin-article-detail .views li"
    ).unbind();
    $(
      ".spreadplugin-article .description-wrapper div.header,.spreadplugin-article-detail .description-wrapper div.header"
    ).unbind();
    $(
      ".spreadplugin-article .product-description-wrapper div.header,.spreadplugin-article-detail .product-description-wrapper div.header"
    ).unbind();
    $(".spreadplugin-design .image-wrapper").unbind();
    $(".spreadplugin-article form,.spreadplugin-article-detail form").unbind();
    $(
      ".spreadplugin-article .edit-wrapper a,.spreadplugin-article-detail .edit-wrapper a"
    ).unbind();
    $(
      ".spreadplugin-article .edit-wrapper-integrated a,.spreadplugin-article-detail .edit-wrapper-integrated a"
    ).unbind();
    $(
      ".spreadplugin-article .details-wrapper a,.spreadplugin-article-detail .details-wrapper a"
    ).unbind();
    $(
      ".spreadplugin-article .image-wrapper,.spreadplugin-article-detail .image-wrapper"
    ).unbind();

    $(
      ".spreadplugin-article .colors li,.spreadplugin-article-detail .colors li"
    ).click(({ currentTarget }) => {
      const id = `#${$(currentTarget)
        .closest(".spreadplugin-article,.spreadplugin-article-detail")
        .attr("id")}`;
      const image = $(`${id} img.preview`);
      // const src = image.attr('src');
      const srczoom = image.attr("data-zoom-image");
      const srczoomData = image.data("elevateZoom");

      appearance = $(currentTarget).attr("value");
      view = $(`${id} #view`).val();
      $(`${id} #appearance`).val(appearance);

      image.attr(
        "src",
        `${image
          .attr("src")
          .replace(/,appearanceId=(\d+)/g, "")
          .replace(/,viewId=(\d+)/g, "")
          .replace(
            /\/views\/(\d+)/g,
            `/views/${view}`
          )},appearanceId=${appearance},viewId=${view}`
      );

      image.attr(
        "data-zoom-image",
        `${srczoom
          .replace(/,appearanceId=(\d+)/g, "")
          .replace(/,viewId=(\d+)/g, "")
          .replace(
            /\/views\/(\d+)/g,
            `/views/${view}`
          )},appearanceId=${appearance},viewId=${view}`
      );

      $(`${id} img.previewview`).each((i, el) => {
        const originalsrc = $(el).attr("src");
        $(el).attr(
          "src",
          `${originalsrc.replace(
            /,appearanceId=(\d+)/g,
            ""
          )},appearanceId=${appearance}`
        );
      });

      if (srczoomData) {
        const url = `${srczoomData.imageSrc
          .replace(/,appearanceId=(\d+)/g, "")
          .replace(/,viewId=(\d+)/g, "")
          .replace(
            /\/views\/(\d+)/g,
            `/views/${view}`
          )},appearanceId=${appearance},viewId=${view}`;
        srczoomData.imageSrc = url;
        srczoomData.zoomImage = url;
        srczoomData.currentImage = url;

        if (srczoomData.zoomWindow) {
          srczoomData.zoomWindow.css({
            backgroundImage: `url('${url}')`,
          });
        }
        if (srczoomData.zoomLens) {
          srczoomData.zoomLens.css({
            backgroundImage: `url('${url}')`,
          });
        }
      }
    });

    $(
      ".spreadplugin-article .views li,.spreadplugin-article-detail .views li"
    ).click(({ currentTarget }) => {
      const id = `#${$(currentTarget)
        .closest(".spreadplugin-article,.spreadplugin-article-detail")
        .attr("id")}`;
      const image = $(`${id} img.preview`);
      const viewType = $(currentTarget).data("view-type");
      const src = image
        .attr("src")
        .replace(/\/v1\/(products|compositions)\//gi, `/v1/${viewType}/`);
      const srczoom = image
        .attr("data-zoom-image")
        .replace(/\/v1\/(products|compositions)\//gi, `/v1/${viewType}/`);
      const srczoomData = image.data("elevateZoom");

      view = $(currentTarget).attr("value");
      appearance = $(`${id} #appearance`).val();
      $(`${id} #view`).val(view);

      image.attr(
        "src",
        `${src
          .replace(/,appearanceId=(\d+)/g, "")
          .replace(/,viewId=(\d+)/g, "")
          .replace(/\/views\/(\d+)/g, `/views/${view}`)
          .replace(
            /,modelId=(\d+)/g,
            ""
          )},appearanceId=${appearance},viewId=${view}`
      );

      image.attr(
        "data-zoom-image",
        `${srczoom
          .replace(/,appearanceId=(\d+)/g, "")
          .replace(/,viewId=(\d+)/g, "")
          .replace(/\/views\/(\d+)/g, `/views/${view}`)
          .replace(
            /,modelId=(\d+)/g,
            ""
          )},appearanceId=${appearance},viewId=${view}`
      );

      if (srczoomData) {
        const url = `${srczoomData.imageSrc
          .replace(/\/v1\/(products|compositions)\//gi, `/v1/${viewType}/`)
          .replace(/,appearanceId=(\d+)/g, "")
          .replace(/,viewId=(\d+)/g, "")
          .replace(/\/views\/(\d+)/g, `/views/${view}`)
          .replace(
            /,modelId=(\d+)/g,
            ""
          )},appearanceId=${appearance},viewId=${view}`;
        srczoomData.imageSrc = url;
        srczoomData.zoomImage = url;
        srczoomData.currentImage = url;
        if (srczoomData.zoomWindow) {
          srczoomData.zoomWindow.css({
            backgroundImage: `url('${url}')`,
          });
        }
        if (srczoomData.zoomLens) {
          srczoomData.zoomLens.css({
            backgroundImage: `url('${url}')`,
          });
        }
      }
    });

    $(
      ".spreadplugin-article .description-wrapper div.header,.spreadplugin-article-detail .description-wrapper div.header"
    ).click(({ currentTarget }) => {
      const par = $(currentTarget).parent().parent().parent();
      const field = $(currentTarget).next();

      if (field.is(":hidden")) {
        par.addClass("activeDescription");
        field.show();
        $(currentTarget).children("a").html(ajax_object.textHideDesc);
      } else {
        par.removeClass("activeDescription");
        $(".description-wrapper div.description").hide();
        $(".description-wrapper div.header a").html(ajax_object.textShowDesc);
      }
    });
    $(
      ".spreadplugin-article .product-description-wrapper div.header,.spreadplugin-article-detail .description-wrapper div.header"
    ).click(({ currentTarget }) => {
      const par = $(currentTarget).parent().parent().parent();
      const field = $(currentTarget).next();

      if (field.is(":hidden")) {
        par.addClass("activeDescription");
        field.show();
        $(currentTarget).children("a").html(ajax_object.textProdHideDesc);
      } else {
        par.removeClass("activeDescription");
        $(".product-description-wrapper div.description").hide();
        $(".product-description-wrapper div.header a").html(
          ajax_object.textProdShowDesc
        );
      }
    });

    $(".spreadplugin-article form,.spreadplugin-article-detail form").submit(
      (event) => {
        event.preventDefault();
        const data = `${$(event.currentTarget).serialize()}&action=myAjax`; // &'+sid
        const form = event.currentTarget;
        const button = $(`#${form.id} input[type=submit]`).not(
          ".add-basket-button"
        );

        button.val(ajax_object.textButtonAdded);

        $.post(
          ajax_object.ajaxLocation,
          data,
          (json) => {
            if (json.c.m == 1) {
              button.val(ajax_object.textButtonAdd);

              if (
                ajax_object.openBasketOnAdd &&
                ajax_object.openBasketOnAdd == 1
              ) {
                $(".spreadplugin-checkout-link").click();
              }
            } else {
              button.val(ajax_object.textButtonFailed);
            }

            refreshCart(json);
          },
          "json"
        );

        return false;
      }
    );

    // integrated edit wrapper
    $(
      ".spreadplugin-article .edit-wrapper-integrated,.spreadplugin-article-detail .edit-wrapper-integrated"
    ).click(({ currentTarget }) => {
      const designid = $(currentTarget).data("designid");
      // const productid = $(currentTarget).data('productid');
      const viewid = $(currentTarget).data("viewid");
      const appearanceid = $(currentTarget).data("appearanceid");
      const producttypeid = $(currentTarget).data("producttypeid");

      $.magnificPopup.open({
        items: {
          type: "inline",
          src: "#spreadplugin-designer-wrapper",
        },
        callbacks: {
          open() {
            $(".mfp-iframe-holder .mfp-content").css(
              "height",
              $(window).height() - 200
            );
            callIntegratedDesigner(
              designid,
              producttypeid,
              appearanceid,
              viewid
            );
          },
          resize() {
            $(".mfp-iframe-holder .mfp-content").css(
              "height",
              $(window).height() - 200
            );
          },
          close() {
            $("#spreadplugin-designer").html("");
          },
        },
      });
    });

    $(".spreadplugin-design .image-wrapper").click(({ currentTarget }) => {
      let id = $(currentTarget).parent().attr("id");
      id = `#${id.replace("design", "designContainer")}`;

      if ($(id).is(":hidden")) {
        $(id).addClass("active");
        $(id).slideDown("slow");
      } else {
        $("#spreadplugin-list .design-container").slideUp("slow", (el) => {
          $(el.currentTarget).removeClass("active");
        });
      }
    });
  }

  // Run actions
  $(".spreadplugin-cart").hide();

  // hide cart when user clicks outside
  $(document).click((e) => {
    if (
      e.target.className !== "spreadplugin-checkout-link" &&
      e.target.className !== "spreadplugin-checkout-link button" &&
      $(".spreadplugin-cart").is(":visible") &&
      !$(".spreadplugin-cart").find(e.target).length
    ) {
      $(".spreadplugin-cart").hide();
    }
  });

  // stops hover lose when hovering min-view select
  $(".spreadplugin-items select").hover((e) => {
    e.stopPropagation();
  });

  function bindHover() {
    $(
      ".spreadplugin-article img.preview,.spreadplugin-article-detail img.preview"
    ).unbind();
    $("div.spreadplugin-article.min-view").unbind();

    // display image caption on top of image
    $(".spreadplugin-design div.image-wrapper").each((i, el) => {
      $(el).hover(
        (e) => {
          $(e.currentTarget)
            .find(".img-caption")
            .stop(true)
            .css("display", "inline-block")
            .animate(
              {
                top: -50,
              },
              {
                queue: false,
                duration: 400,
              }
            );
        },
        (e) => {
          $(e.currentTarget).find(".img-caption").stop(true).hide().animate({
            top: 0,
          });
        }
      );
    });

    // Articles zoom image
    if (ajax_object.zoomActivated == 1) {
      $(
        ".spreadplugin-article img.preview,.spreadplugin-article-detail img.preview"
      ).hover(({ currentTarget }) => {
        $(currentTarget).elevateZoom(ajax_object.zoomConfig);
      });
    }

    // socials
    $(
      ".spreadplugin-article ul.soc-icons a,.spreadplugin-article-detail ul.soc-icons a"
    ).hover(
      ({ currentTarget }) => {
        $(currentTarget)
          .parent()
          .css("background-color", $(this).attr("data-color"));
      },
      ({ currentTarget }) => {
        $(currentTarget).parent().removeAttr("style");
      }
    );

    $("div.spreadplugin-article.min-view").hover(
      ({ currentTarget }) => {
        $(currentTarget).addClass("active");
      },
      ({ currentTarget }) => {
        $(currentTarget).removeClass("active");
      }
    );

    // hover modal effekt when min-view
    // if (!$.browser.msie || parseInt($.browser.version, 10) > 8) {
    const onMouseOutOpacity = 1;
    $("div.spreadplugin-article.min-view")
      .css("opacity", onMouseOutOpacity)
      .hover(
        ({ currentTarget }) => {
          $(currentTarget)
            .prevAll()
            .stop()
            .not(".clear,#infscr-loading")
            .fadeTo("slow", 0.6);
          $(currentTarget)
            .nextAll()
            .stop()
            .not(".clear,#infscr-loading")
            .fadeTo("slow", 0.6);
        },
        ({ currentTarget }) => {
          $(currentTarget)
            .prevAll()
            .stop()
            .not(".clear,#infscr-loading")
            .fadeTo("slow", onMouseOutOpacity);
          $(currentTarget)
            .nextAll()
            .stop()
            .not(".clear,#infscr-loading")
            .fadeTo("slow", onMouseOutOpacity);
        }
      );
    // }
  }

  // reload caption
  $(window).resize(() => {
    $(".img-caption").hide();
  });

  if (ajax_object.infiniteScroll == 1) {
    // infinity scroll
    $("#spreadplugin-list").infinitescroll(
      {
        nextSelector: "#spreadplugin-items #pagination a",
        navSelector: "#spreadplugin-items #pagination",
        itemSelector: `#spreadplugin-list ${infiniteItemSel}`,
        loading: {
          img: ajax_object.loadingImage,
          msgText: ajax_object.loadingMessage,
          finishedMsg: ajax_object.loadingFinishedMessage,
        },
        animate: true,
        debug: false,
        bufferPx: 40,
      },
      (arrayOfNewElems) => {
        bindClick();
        bindHover();

        if (ajax_object.lazyLoad == 1) {
          $("img.lazyimg").lazyload({
            effect: "fadeIn",
          });
        }

        const $newElems = $(arrayOfNewElems);
        $("#spreadplugin-list").isotope("appended", $newElems, true);
      }
    );
  }

  $("#spreadplugin-items #articleSortBy").change(({ currentTarget }) => {
    sor = $(currentTarget).val();

    document.location = `${
      ajax_object.pageLink + sep
    }pagesp=${paged}&articleSortBy=${sor}`;
  });

  $(".spreadplugin-checkout-link").click((e) => {
    e.preventDefault();

    const cart = $(e.currentTarget).parent().next(".spreadplugin-cart");

    if (cart.attr("id") === "spreadplugin-widget-cart") {
      cart.css("position", "relative");
    }

    if (cart.is(":hidden")) {
      cart.css("display", "inline-block");
    } else {
      cart.hide();
    }
  });

  // &'+sid
  $.post(
    ajax_object.ajaxLocation,
    "action=myAjax",
    (json) => {
      refreshCart(json);
    },
    "json"
  );

  bindClick();
  bindHover();
  if (ajax_object.lazyLoad == 1) {
    $("img.lazyimg").lazyload({
      effect: "fadeIn",
    });
  }

  $(window).trigger("resize");
});
