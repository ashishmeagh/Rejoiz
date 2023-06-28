"function" !== typeof Object.create && (Object.create = function (f) {
	function k() {}
	k.prototype = f;
	return new k
});
(function (f, k, q) {
	var g = {
		global: {
			isNoDrag: 0,
			isDrag: 0,
			isZoom: !1,
			leftDirection: !0,
			rightDirection: !0,
			topBottomDirection: !1,
			newX: 0,
			newY: 0,
			open: null
		},
		init: function (a, b) {
			this.$elem = f(b);
			this.options = f.extend({}, f.fn.rwaltzGallery.options, a);
			this.miniOptions = f.extend({}, f.fn.rwaltzGallery.options.miniSlider);
			a && a.miniSlider && (this.miniOptions = f.extend(this.miniOptions, a.miniSlider));
			this.galleryOptions = f.extend({}, f.fn.rwaltzGallery.options.gallerySlider);
			a && a.gallerySlider && (this.galleryOptions = f.extend(this.galleryOptions,
				a.gallerySlider));
			var c = this.$elem.find("img:nth-of-type(1)").data("medium-img");
			this.$elem.prepend('<div class="rwaltz-medium-wrap"><a class="rwaltz-view-medium-img"><img src="' + c + '" alt=""></a><div class="scale-ico"></div></div>');
			this.$elem.append('<div class="is-mobile"></div>');
			this.$elem.find(".prod-carousel").prodCarouselE(this.miniOptions);
			this.$medium = this.$elem.find(".rwaltz-medium-wrap");
			this.showMediumImg();
			this.openGallery()
		},
		showMediumImg: function () {
			var a = this,
				b = {
					slideIn: function (b, d) {
						b >
							d && a.$medium.addClass("slideInNext");
						b < d && a.$medium.addClass("slideInPrev")
					},
					slideOut: function (b, d) {
						b > d && a.$medium.addClass("slideOutNext");
						b < d && a.$medium.addClass("slideOutPrev")
					},
					scaleIn: function () {
						a.$medium.addClass("scaleIn")
					},
					scaleOut: function () {
						a.$medium.addClass("scaleOut")
					}
				};
			a.$elem.on("click", ".prod-carousel." + a.miniOptions.theme + " .prod-item img", function (c) {

				//Added by Shital.v set sku image no on image click...	
				var sku = $(this).attr('data-imgsku');
				$("#sku_num").val(sku);
				//End....

				c.preventDefault();
				if (0 == a.global.isNoDrag) {
					var d = f(this).data("medium-img");
					c = f("<img>", {
						src: d
					});
					var g = f(this).parent().index(),
						h = a.$elem.find(".prod-item.active").index();
					if (g != h) {
						var e = setTimeout(function () {
							a.$medium.append('<div class="loading">Loading...</div>')
						}, 500);

						c.on("load",function(){
						// c.load(function () {
							clearTimeout(e);
							a.$medium.find(".loading").length && a.$medium.find(".loading").remove();
							if (0 == a.options.changeMediumStyle) a.$elem.find(".rwaltz-view-medium-img img").attr("src", d);
							else {
								iterations = 0;
								if (1 == a.options.changeMediumStyle) {
									var c = 1 + Math.floor(4 * Math.random());
									1 == c && b.slideIn(g, h);
									2 == c && b.slideOut(g, h);
									3 == c && b.scaleIn();
									4 == c && b.scaleOut()
								}
								"slideIn" == a.options.changeMediumStyle &&
									b.slideIn(g, h);
								"slideOut" == a.options.changeMediumStyle && b.slideOut(g, h);
								"scaleIn" == a.options.changeMediumStyle && b.scaleIn();
								"scaleOut" == a.options.changeMediumStyle && b.scaleOut();
								a.$medium.append('<a class="rwaltz-view-medium-img ea-show"><img src="' + d + '" alt=""></a>');
								a.$medium.find("a").css(m.addCssSpeed(a.options.changeMediumSpeed));
								setTimeout(function () {
									a.$medium.find("a").not(".ea-show").addClass("ea-hide");
									a.$medium.find("a").removeClass("ea-show")
								}, 60);
								setTimeout(function () {
									a.$medium.find("a.ea-hide").remove();
									a.$medium.removeClass("slideInNext").removeClass("slideInPrev").removeClass("slideOutNext").removeClass("slideOutPrev").removeClass("scaleIn").removeClass("scaleOut")
								}, a.options.changeMediumSpeed + 60)
							}
						})
					}
					a.$elem.find(".prod-item").removeClass("active");
					f(this).parent().addClass("active")
				}
			})
		},
		openGallery: function () {
			var a = this;
			a.$elem.on("click", ".rwaltz-medium-wrap", function (b) {
				b.preventDefault();
				a.createGallery()
			})
		},
		createGallery: function () {
			var a = "",
				b = this.$elem.find("." + this.miniOptions.theme);
			this.numberOfimages =
				b.find(".prod-item").length;
			this.currentImage = b.find(" .prod-item.active").index() + 1;
			b.find(".prod-item").each(function () {
				var b = f(this),
					d = b.find("img").data("big-img");
				a = (b = b.find("img").data("title")) ? a + '<div class="rwaltz-item" data-scale="1"><img src="' + d + '"><div class="title"><span>' + b + "</span></div></div>" : a + '<div class="rwaltz-item" data-scale="1"><img src="' + d + '"></div>'
			});
			this.$elem.append('<div class="rwaltz-open-gallery"><div class="top-controls"><div class="ea-progress"><div class="ea-current">' +
				this.currentImage + '</div><div>/</div><div class="ea-all">' + this.numberOfimages + '</div></div><div class="rwaltz-close"><span></span></div></div><div class="rwaltz-prev"><span></span></div><div class="view-big-img"><div class="prod-carousel">' + a + '</div></div><div class="rwaltz-next"><span></span></div><div class="bottom-controls"><div class="rwaltz-scale"><div class="ea-zoom ea-minus"><span></span></div><div class="ea-magnifier"></div><div class="ea-zoom ea-plus"><span></span></div></div></div></div>');
			this.$gall = this.$elem.find(".rwaltz-open-gallery");
			this.options.bottomControlLine && this.$gall.addClass("rwaltz-bottom-line");
			"transform" == this.options.openGalleryStyle && (this.$gall.addClass("transform"), this.$gall.find(".top-controls, .bottom-controls, .view-big-img, .rwaltz-prev, .rwaltz-next").addClass("ea-hide"));
			this.$gall.addClass(this.options.theme);
			1 == this.currentImage ? this.$gall.find(".rwaltz-prev").addClass("disabled") : this.currentImage == b.find(".prod-item").length && this.$gall.find(".rwaltz-next").addClass("disabled");
			this.global.open = this.$gall;
			this.showGallery()
		},
		showGallery: function () {
			var a = this,
				b = f("body").width(),
				c = f("body").outerWidth();
			b != c && (f("body").css({
				"padding-right": 0
			}), a.bodyRightPadding = c - f("body").width(), a.bodyRightPadding = c - b - a.bodyRightPadding);
			f("body").css({
				overflow: "hidden"
			});
			b = f("body").outerWidth() - c;
			void 0 != a.bodyRightPadding ? f("body").css({
				"padding-right": b + a.bodyRightPadding
			}) : f("body").css({
				"padding-right": b
			});
			a.$gall.css(m.addCssSpeed(a.options.openGallerySpeed));
			"show" == a.options.openGalleryStyle &&
				setTimeout(function () {
					a.$gall.css({
						opacity: 1
					})
				}, 10);
			"transform" == a.options.openGalleryStyle && (setTimeout(function () {
				a.$gall.css({
					opacity: 1,
					"-webkit-transform": "scale3d(1, 1, 1)",
					"-o-transform": "scale3d(1, 1, 1)",
					"-ms-transform": "scale3d(1, 1, 1)",
					"-moz-transform": "scale3d(1, 1, 1)",
					transform: "scale3d(1, 1, 1)"
				})
			}, 10), setTimeout(function () {
				a.$gall.find(".top-controls, .bottom-controls").removeClass("ea-hide")
			}, a.options.openGallerySpeed + 400), setTimeout(function () {
				function b() {
					c += 1;
					m.completeImg(a.$gall.find(".prod-item.active img").get(0)) ?
						a.$gall.find(".view-big-img").removeClass("ea-hide") : 100 >= c ? k.setTimeout(b, 100) : a.$gall.find(".view-big-img").removeClass("ea-hide")
				}
				var c = 0;
				b()
			}, a.options.openGallerySpeed + 800), setTimeout(function () {
				a.$elem.find(".transform").length && a.$gall.find(".rwaltz-prev, .rwaltz-next").removeClass("ea-hide")
			}, a.options.openGallerySpeed + 1300), setTimeout(function () {
				a.$elem.find(".transform").length && a.$gall.removeClass("transform")
			}, a.options.openGallerySpeed + 2300));
			a.$gall.bind("touchmove", function (a) {
				a.preventDefault()
			});
			a.$gall.find(".prod-carousel").prodCarouselE(a.galleryOptions);
			a.prodGallery = a.$gall.find(".prod-carousel").data("prodCarouselE");
			a.prodGallery.jumpTo(a.currentImage - 1);
			a.whenGalleryOpened()
		},
		whenGalleryOpened: function () {
			this.$gall.find(".top-controls, .bottom-controls").css(m.addCssSpeed(this.options.hideControlsSpeed));
			this.galleryEvents();
			this.mouseDown();
			this.gestures();
			this.response()
		},
		galleryEvents: function () {
			var a = this;
			a.$gall.on("click", ".rwaltz-prev span", function () {
				a.prodGallery.prev()
			});
			a.$gall.on("click",
				".rwaltz-next span",
				function () {
					a.prodGallery.next()
				});
			a.$gall.on("click", ".view-big-img", function () {
				a.hideControls()
			});
			a.$gall.on("click", ".rwaltz-close", function () {
				a.close()
			})
		},
		mouseDown: function () {
			this.$gall.find(".rwaltz-item").on("mousedown", function () {
				f(this).addClass("mouse-down")
			}).on("mouseup", function () {
				f(this).removeClass("mouse-down")
			})
		},
		gestures: function () {
			function a(a) {
				var b = e.$gall.find(".prod-item.active").offset();
				if (void 0 !== a.touches) return f.map(a.touches, function (a) {
					return {
						x: a.pageX -
							b.left,
						y: a.pageY - b.top
					}
				});
				if (void 0 === a.touches) {
					if (void 0 !== a.pageX) return f.map(a, function (a) {
						return {
							x: a.pageX,
							y: a.pageY
						}
					});
					if (void 0 === a.pageX) return f.map(a, function (a) {
						return {
							x: a.clientX,
							y: a.clientY
						}
					})
				}
			}

			function b(a, b) {
				var e, c;
				e = a.x - b.x;
				c = a.y - b.y;
				return Math.sqrt(e * e + c * c)
			}

			function c(a, e) {
				var c = b(a[0], a[1]);
				return b(e[0], e[1]) / c
			}

			function d(a) {
				"on" === a ? (e.$gall.on(e.ev_types.moveZoom, ".prod-item", g), e.$gall.on(e.ev_types.endZoom, ".prod-item", h)) : "off" === a && (e.$gall.off(e.ev_types.moveZoom, ".prod-item",
					g), e.$gall.off(e.ev_types.endZoom, ".prod-item", h))
			}

			function g(b) {
				b = b.originalEvent || b || k.event;
				var d = f(this).find(".rwaltz-item");
				l < e.options.maxZoom ? (l = oldScale * c(r, a(b)), l > e.options.maxZoom && (l = e.options.maxZoom), e.newPos(d, l), e.updateImg(d, l, e.global.newX, e.global.newY), e.direction(d, l)) : (oldScale = e.options.maxZoom, l = oldScale * c(r, a(b)), r = a(b))
			}

			function h(a) {
				var b = f(this).find(".rwaltz-item");
				e.global.isZoom = !1;
				l > e.options.maxZoom && (l = e.options.maxZoom);
				if (1 > l || .01 > Math.abs(l - 1)) l = 1, b.css(m.addCssSpeed(300)),
					e.updateImg(b, l, e.global.newX, e.global.newY), setTimeout(function () {
						b.css(m.removeTransition());
						e.direction(b, l)
					}, 300), e.$gall.find(".top-controls.ea-hide").length || e.showTitle(b.find(".title"));
				b.data("scale", l).attr("data-scale", l);
				e.direction(b, l);
				d("off")
			}
			var e = this,
				r = null,
				l, t;
			e.ev_types = {
				startZoom: "touchstart",
				moveZoom: "touchmove",
				endZoom: "touchend touchcancel"
			};
			e.$gall.on("click", ".rwaltz-scale .ea-plus", function () {
				var a = e.$gall.find(".prod-item.active .rwaltz-item");
				l = a.data("scale");
				0 != l % 1 && (l =
					parseInt(l, 10));
				l < e.options.maxZoom && (1 == l && e.hideTitle(a.find(".title")), clearTimeout(k.remTran), a.css(m.addCssSpeed(200)), l++, e.updateImg(a, l, e.global.newX, e.global.newY), k.remTran = setTimeout(function () {
					a.css(m.removeTransition());
					e.direction(a, l)
				}, 200), a.data("scale", l).attr("data-scale", l))
			});
			e.$gall.on("click", ".rwaltz-scale .ea-minus", function () {
				var a = e.$gall.find(".prod-item.active .rwaltz-item");
				l = a.data("scale");
				0 != l % 1 ? l = parseInt(l, 10) : 1 < l && l--;
				1 == l && e.showTitle(a.find(".title"));
				e.newPos(a,
					l);
				clearTimeout(k.remTran);
				a.css(m.addCssSpeed(200));
				e.updateImg(a, l, e.global.newX, e.global.newY);
				k.remTran = setTimeout(function () {
					a.css(m.removeTransition());
					e.direction(a, l)
				}, 200);
				a.data("scale", l).attr("data-scale", l)
			});
			e.$gall.on(e.ev_types.startZoom, ".prod-item", function (c) {
				c = c.originalEvent || c || k.event;
				var h = f(this).find(".rwaltz-item");
				if (0 != e.global.isDrag) return !1;
				2 === c.touches.length && (c.preventDefault(), e.global.isZoom = !0, r = a(c), b(r[0], a(c)[1]), oldScale = l = t = h.data("scale"), h.find(".title.hideTitle").length ||
					h.find(".title.hideTitleMobile").length || e.hideTitle(h.find(".title")), d("on"))
			})
		},
		updateImg: function (a, b, c, d) {
			b = "scale3d(" + b + ", " + b + ", 1)";
			c = "translate3d(" + c + "px, " + d + "px, 0px)";
			a.css({
				"-webkit-transform": b + c,
				"-o-transform": b + c,
				"-ms-transform": b + c,
				"-moz-transform": b + c,
				transform: b + c
			})
		},
		direction: function (a, b) {
			var c = a.find("img");
			c.offset().left + 3 >= a.closest(".prod-item").offset().left ? this.global.leftDirection = !0 : this.global.leftDirection = !1;
			c.offset().left + c.width() * b - 3 <= a.closest(".prod-item").offset().left +
				a.closest(".prod-item").width() ? this.global.rightDirection = !0 : this.global.rightDirection = !1;
			c.height() * b - 2 > a.closest(".prod-item").height() ? this.global.topBottomDirection = !0 : this.global.topBottomDirection = !1
		},
		newPos: function (a, b) {
			var c = a.find("img");
			if (c.width() * b > a.closest(".prod-item").width()) {
				var d = (c.width() * b - a.closest(".prod-item").width()) / 2,
					f = this.global.newX * b;
				f > d && (this.global.newX = (c.width() * b - a.closest(".prod-item").width()) / (2 * b)); - f > d && (this.global.newX = (a.closest(".prod-item").width() - c.width() *
					b) / (2 * b))
			} else this.global.newX = 0;
			c.height() * b > a.closest(".prod-item").height() ? (d = (c.height() * b - a.closest(".prod-item").height()) / 2, f = this.global.newY * b, f > d && (this.global.newY = (c.height() * b - a.closest(".prod-item").height()) / (2 * b)), -f > d && (this.global.newY = (a.closest(".prod-item").height() - c.height() * b) / (2 * b))) : this.global.newY = 0
		},
		response: function () {
			var a = this,
				b, c, d;
			c = f(k).width();
			d = f(k).height();
			a.resizer = function () {
				if (!a.$gall) return !1;
				if (f(k).width() !== c || f(k).height() !== d) k.clearTimeout(b), b = k.setTimeout(function () {
					c =
						f(k).width();
					d = f(k).height();
					var b = a.$gall.find(".prod-item.active .rwaltz-item"),
						h = b.data("scale");
					a.newPos(b, h);
					a.updateImg(b, h, a.global.newX, a.global.newY);
					a.direction(b, h);
					b = a.$gall.find(".prod-item");
					a.isMobile() ? b.each(function () {
						var a = f(this).find(".title");
						a.hasClass("hideTitle") && (a.addClass("hideTitleMobile"), a.removeClass("hideTitle"))
					}) : b.each(function () {
						var a = f(this).find(".title");
						a.hasClass("hideTitleMobile") && (a.addClass("hideTitle"), a.removeClass("hideTitleMobile"))
					});
					!a.isMobile() &&
						a.$gall.find(".top-controls").hasClass("ea-hide") && (a.$gall.find(".top-controls, .bottom-controls").toggleClass("ea-hide"), b.each(function () {
							1 == f(this).find(".rwaltz-item").data("scale") && a.showTitle(f(this).find(".title"))
						}))
				}, 200)
			};
			f(k).resize(a.resizer)
		},
		hideTitle: function (a) {
			var b = this;
			if (!a.length) return !1;
			a.css(m.addCssSpeed(b.options.hideControlsSpeed));
			b.isMobile() ? a.addClass("hideTitleMobile") : a.addClass("hideTitle");
			setTimeout(function () {
					a.css(m.removeTransition(b.options.hideControlsSpeed))
				},
				200)
		},
		showTitle: function (a) {
			var b = this;
			if (!a.length) return !1;
			a.css(m.addCssSpeed(b.options.hideControlsSpeed));
			b.isMobile() ? a.removeClass("hideTitleMobile") : a.removeClass("hideTitle");
			setTimeout(function () {
				a.css(m.removeTransition(b.options.hideControlsSpeed))
			}, 200)
		},
		hideControls: function () {
			var a = this;
			if (a.isMobile() && 0 == a.global.isNoDrag) {
				a.$gall.find(".top-controls, .bottom-controls").toggleClass("ea-hide");
				var b = a.$gall.find(".prod-item");
				a.$gall.find(".top-controls").hasClass("ea-hide") ? b.each(function () {
					1 ==
						f(this).find(".rwaltz-item").data("scale") && a.hideTitle(f(this).find(".title"))
				}) : b.each(function () {
					1 == f(this).find(".rwaltz-item").data("scale") && a.showTitle(f(this).find(".title"))
				})
			}
		},
		close: function () {
			var a = this;
			a.$gall.css({
				opacity: 0
			});
			a.global.isZoom = !1;
			a.global.leftDirection = !0;
			a.global.rightDirection = !0;
			a.global.topBottomDirection = !1;
			a.global.open = null;
			setTimeout(function () {
				a.prodGallery.destroy();
				a.$gall.remove();
				a.$gall = void 0;
				void 0 != a.bodyRightPadding ? f("body").css({
					"padding-right": a.bodyRightPadding,
					overflow: "auto"
				}) : f("body").css({
					"padding-right": 0,
					overflow: "auto"
				})
			}, a.options.openGallerySpeed)
		},
		isMobile: function () {
			return this.$elem.find(".is-mobile").is(":visible") ? !0 : !1
		}
	};
	f.fn.rwaltzGallery = function (a) {
		return this.each(function () {
			Object.create(g).init(a, this)
		})
	};
	f.fn.rwaltzGallery.options = {
		maxZoom: 4,
		changeMediumStyle: !1,
		changeMediumSpeed: 600,
		openGalleryStyle: "show",
		openGallerySpeed: 300,
		hideControlsSpeed: 200,
		theme: "dark",
		bottomControlLine: !1,
		miniSlider: {
			navigation: !0,
			pagination: !1,
			navigationText: !1,
			rewindNav: !1,
			theme: "mini-slider",
			responsiveBaseWidth: ".rwaltz-gallery",
			itemsCustom: [
				[0, 1],
				[250, 2],
				[450, 3],
				[650, 4],
				[850, 5],
				[1050, 6],
				[1250, 7],
				[1450, 8]
			],
			afterInit: function (a) {
				a.find(".prod-item:first-child").addClass("active")
			}
		},
		gallerySlider: {
			singleItem: !0,
			navigation: !1,
			pagination: !1,
			rewindNav: !1,
			addClassActive: !0,
			theme: "gallery-slider"
		}
	};
	var m = {
		init: function (a, b) {
			this.$elem = f(b);
			this.options = f.extend({}, f.fn.prodCarouselE.options, this.$elem.data(), a);
			this.userOptions = a;
			this.loadContent()
		},
		loadContent: function () {
			function a(a) {
				var c,
					f = "";
				if ("function" === typeof b.options.jsonSuccess) b.options.jsonSuccess.apply(this, [a]);
				else {
					for (c in a.prod) a.prod.hasOwnProperty(c) && (f += a.prod[c].item);
					b.$elem.html(f)
				}
				b.logIn()
			}
			var b = this,
				c;
			"function" === typeof b.options.beforeInit && b.options.beforeInit.apply(this, [b.$elem]);
			"string" === typeof b.options.jsonPath ? (c = b.options.jsonPath, f.getJSON(c, a)) : b.logIn()
		},
		logIn: function () {
			this.$elem.data("prod-originalStyles", this.$elem.attr("style"));
			this.$elem.data("prod-originalClasses", this.$elem.attr("class"));
			this.$elem.css({
				opacity: 0
			});
			this.orignalItems = this.options.items;
			this.checkBrowser();
			this.wrapperWidth = 0;
			this.checkVisible = null;
			this.setVars()
		},
		setVars: function () {
			if (0 === this.$elem.children().length) return !1;
			this.baseClass();
			this.eventTypes();
			this.$userItems = this.$elem.children();
			this.itemsAmount = this.$userItems.length;
			this.wrapItems();
			this.$prodItems = this.$elem.find(".prod-item");
			this.$prodWrapper = this.$elem.find(".prod-wrapper");
			this.playDirection = "next";
			this.prevItem = 0;
			this.prevArr = [0];
			this.currentItem =
				0;
			this.customEvents();
			this.onStartup()
		},
		onStartup: function () {
			this.updateItems();
			this.calculateAll();
			this.buildControls();
			this.updateControls();
			this.response();
			this.moveEvents();
			this.stopOnHover();
			this.prodStatus();
			!1 !== this.options.transitionStyle && this.transitionTypes(this.options.transitionStyle);
			!0 === this.options.autoPlay && (this.options.autoPlay = 5E3);
			this.play();
			this.$elem.find(".prod-wrapper").css("display", "block");
			this.$elem.is(":visible") ? this.$elem.css("opacity", 1) : this.watchVisibility();
			this.onstartup = !1;
			this.eachMoveUpdate();
			"function" === typeof this.options.afterInit && this.options.afterInit.apply(this, [this.$elem])
		},
		eachMoveUpdate: function () {
			!0 === this.options.lazyLoad && this.lazyLoad();
			!0 === this.options.autoHeight && this.autoHeight();
			this.onVisibleItems();
			"function" === typeof this.options.afterAction && this.options.afterAction.apply(this, [this.$elem])
		},
		updateVars: function () {
			"function" === typeof this.options.beforeUpdate && this.options.beforeUpdate.apply(this, [this.$elem]);
			this.watchVisibility();
			this.updateItems();
			this.calculateAll();
			this.updatePosition();
			this.updateControls();
			this.eachMoveUpdate();
			"function" === typeof this.options.afterUpdate && this.options.afterUpdate.apply(this, [this.$elem])
		},
		reload: function () {
			var a = this;
			k.setTimeout(function () {
				a.updateVars()
			}, 0)
		},
		watchVisibility: function () {
			var a = this;
			if (!1 === a.$elem.is(":visible")) a.$elem.css({
				opacity: 0
			}), k.clearInterval(a.autoPlayInterval), k.clearInterval(a.checkVisible);
			else return !1;
			a.checkVisible = k.setInterval(function () {
				a.$elem.is(":visible") && (a.reload(),
					a.$elem.animate({
						opacity: 1
					}, 200), k.clearInterval(a.checkVisible))
			}, 500)
		},
		wrapItems: function () {
			this.$userItems.wrapAll('<div class="prod-wrapper">').wrap('<div class="prod-item"></div>');
			this.$elem.find(".prod-wrapper").wrap('<div class="prod-wrapper-outer">');
			this.wrapperOuter = this.$elem.find(".prod-wrapper-outer");
			this.$elem.css("display", "block")
		},
		baseClass: function () {
			var a = this.$elem.hasClass(this.options.baseClass),
				b = this.$elem.hasClass(this.options.theme);
			a || this.$elem.addClass(this.options.baseClass);
			b || this.$elem.addClass(this.options.theme)
		},
		updateItems: function () {
			var a, b;
			if (!1 === this.options.responsive) return !1;
			if (!0 === this.options.singleItem) return this.options.items = this.orignalItems = 1, this.options.itemsCustom = !1, this.options.itemsDesktop = !1, this.options.itemsDesktopSmall = !1, this.options.itemsTablet = !1, this.options.itemsTabletSmall = !1, this.options.itemsMobile = !1;
			a = f(this.options.responsiveBaseWidth).width();
			a > (this.options.itemsDesktop[0] || this.orignalItems) && (this.options.items = this.orignalItems);
			if (!1 !== this.options.itemsCustom)
				for (this.options.itemsCustom.sort(function (a, b) {
						return a[0] - b[0]
					}), b = 0; b < this.options.itemsCustom.length; b += 1) this.options.itemsCustom[b][0] <= a && (this.options.items = this.options.itemsCustom[b][1]);
			else a <= this.options.itemsDesktop[0] && !1 !== this.options.itemsDesktop && (this.options.items = this.options.itemsDesktop[1]), a <= this.options.itemsDesktopSmall[0] && !1 !== this.options.itemsDesktopSmall && (this.options.items = this.options.itemsDesktopSmall[1]), a <= this.options.itemsTablet[0] &&
				!1 !== this.options.itemsTablet && (this.options.items = this.options.itemsTablet[1]), a <= this.options.itemsTabletSmall[0] && !1 !== this.options.itemsTabletSmall && (this.options.items = this.options.itemsTabletSmall[1]), a <= this.options.itemsMobile[0] && !1 !== this.options.itemsMobile && (this.options.items = this.options.itemsMobile[1]);
			this.options.items > this.itemsAmount && !0 === this.options.itemsScaleUp && (this.options.items = this.itemsAmount)
		},
		response: function () {
			var a = this,
				b, c;
			if (!0 !== a.options.responsive) return !1;
			c =
				f(k).width();
			a.resizer = function () {
				f(k).width() !== c && (!1 !== a.options.autoPlay && k.clearInterval(a.autoPlayInterval), k.clearTimeout(b), b = k.setTimeout(function () {
					c = f(k).width();
					a.updateVars()
				}, a.options.responsiveRefreshRate))
			};
			f(k).resize(a.resizer)
		},
		updatePosition: function () {
			this.jumpTo(this.currentItem);
			!1 !== this.options.autoPlay && this.checkAp()
		},
		appendItemsSizes: function () {
			var a = this,
				b = 0,
				c = a.itemsAmount - a.options.items;
			a.$prodItems.each(function (d) {
				var g = f(this);
				g.css({
					width: a.itemWidth
				}).data("prod-item",
					Number(d));
				if (0 === d % a.options.items || d === c) d > c || (b += 1);
				g.data("prod-roundPages", b)
			})
		},
		appendWrapperSizes: function () {
			this.$prodWrapper.css({
				width: this.$prodItems.length * this.itemWidth * 2,
				left: 0
			});
			this.appendItemsSizes()
		},
		calculateAll: function () {
			this.calculateWidth();
			this.appendWrapperSizes();
			this.loops();
			this.max()
		},
		calculateWidth: function () {
			this.itemWidth = Math.round(this.$elem.width() / this.options.items)
		},
		max: function () {
			var a = -1 * (this.itemsAmount * this.itemWidth - this.options.items * this.itemWidth);
			this.options.items >
				this.itemsAmount ? this.maximumPixels = a = this.maximumItem = 0 : (this.maximumItem = this.itemsAmount - this.options.items, this.maximumPixels = a);
			return a
		},
		min: function () {
			return 0
		},
		loops: function () {
			var a = 0,
				b = 0,
				c, d;
			this.positionsInArray = [0];
			this.pagesInArray = [];
			for (c = 0; c < this.itemsAmount; c += 1) b += this.itemWidth, this.positionsInArray.push(-b), !0 === this.options.scrollPerPage && (d = f(this.$prodItems[c]), d = d.data("prod-roundPages"), d !== a && (this.pagesInArray[a] = this.positionsInArray[c], a = d))
		},
		buildControls: function () {
			if (!0 ===
				this.options.navigation || !0 === this.options.pagination) this.prodControls = f('<div class="prod-controls"/>').toggleClass("clickable", !this.browser.isTouch).appendTo(this.$elem);
			!0 === this.options.pagination && this.buildPagination();
			!0 === this.options.navigation && this.buildButtons()
		},
		buildButtons: function () {
			var a = this,
				b = f('<div class="prod-buttons"/>');
			a.prodControls.append(b);
			a.buttonPrev = f("<div/>", {
				"class": "prod-prev",
				html: a.options.navigationText[0] || ""
			});
			a.buttonNext = f("<div/>", {
				"class": "prod-next",
				html: a.options.navigationText[1] ||
					""
			});
			b.append(a.buttonPrev).append(a.buttonNext);
			b.on("touchstart.prodControls mousedown.prodControls", 'div[class^="prod"]', function (a) {
				a.preventDefault()
			});
			b.on("touchend.prodControls mouseup.prodControls", 'div[class^="prod"]', function (b) {
				b.preventDefault();
				f(this).hasClass("prod-next") ? a.next() : a.prev()
			})
		},
		buildPagination: function () {
			var a = this;
			a.paginationWrapper = f('<div class="prod-pagination"/>');
			a.prodControls.append(a.paginationWrapper);
			a.paginationWrapper.on("touchend.prodControls mouseup.prodControls",
				".prod-page",
				function (b) {
					b.preventDefault();
					Number(f(this).data("prod-page")) !== a.currentItem && a.goTo(Number(f(this).data("prod-page")), !0)
				})
		},
		updatePagination: function () {
			var a, b, c, d, g, h;
			if (!1 === this.options.pagination) return !1;
			this.paginationWrapper.html("");
			a = 0;
			b = this.itemsAmount - this.itemsAmount % this.options.items;
			for (d = 0; d < this.itemsAmount; d += 1) 0 === d % this.options.items && (a += 1, b === d && (c = this.itemsAmount - this.options.items), g = f("<div/>", {
				"class": "prod-page"
			}), h = f("<span></span>", {
				text: !0 === this.options.paginationNumbers ?
					a : "",
				"class": !0 === this.options.paginationNumbers ? "prod-numbers" : ""
			}), g.append(h), g.data("prod-page", b === d ? c : d), g.data("prod-roundPages", a), this.paginationWrapper.append(g));
			this.checkPagination()
		},
		checkPagination: function () {
			var a = this;
			if (!1 === a.options.pagination) return !1;
			a.paginationWrapper.find(".prod-page").each(function () {
				f(this).data("prod-roundPages") === f(a.$prodItems[a.currentItem]).data("prod-roundPages") && (a.paginationWrapper.find(".prod-page").removeClass("active"), f(this).addClass("active"))
			})
		},
		checkNavigation: function () {
			if (!1 === this.options.navigation) return !1;
			!1 === this.options.rewindNav && (0 === this.currentItem && 0 === this.maximumItem ? (this.buttonPrev.addClass("disabled"), this.buttonNext.addClass("disabled")) : 0 === this.currentItem && 0 !== this.maximumItem ? (this.buttonPrev.addClass("disabled"), this.buttonNext.removeClass("disabled")) : this.currentItem === this.maximumItem ? (this.buttonPrev.removeClass("disabled"), this.buttonNext.addClass("disabled")) : 0 !== this.currentItem && this.currentItem !== this.maximumItem &&
				(this.buttonPrev.removeClass("disabled"), this.buttonNext.removeClass("disabled")))
		},
		updateControls: function () {
			this.updatePagination();
			this.checkNavigation();
			this.prodControls && (this.options.items >= this.itemsAmount ? this.prodControls.hide() : this.prodControls.show())
		},
		destroyControls: function () {
			this.prodControls && this.prodControls.remove()
		},
		next: function (a) {
			if (this.isTransition) return !1;
			this.currentItem += !0 === this.options.scrollPerPage ? this.options.items : 1;
			if (this.currentItem > this.maximumItem + (!0 === this.options.scrollPerPage ?
					this.options.items - 1 : 0))
				if (!0 === this.options.rewindNav) this.currentItem = 0, a = "rewind";
				else return this.currentItem = this.maximumItem, !1;
			this.goTo(this.currentItem, a)
		},
		prev: function (a) {
			if (this.isTransition) return !1;
			this.currentItem = !0 === this.options.scrollPerPage && 0 < this.currentItem && this.currentItem < this.options.items ? 0 : this.currentItem - (!0 === this.options.scrollPerPage ? this.options.items : 1);
			if (0 > this.currentItem)
				if (!0 === this.options.rewindNav) this.currentItem = this.maximumItem, a = "rewind";
				else return this.currentItem =
					0, !1;
			this.goTo(this.currentItem, a)
		},
		goTo: function (a, b, c) {
			var d = this;
			if (d.isTransition) return !1;
			"function" === typeof d.options.beforeMove && d.options.beforeMove.apply(this, [d.$elem]);
			a >= d.maximumItem ? a = d.maximumItem : 0 >= a && (a = 0);
			d.currentItem = d.prod.currentItem = a;
			if (!1 !== d.options.transitionStyle && "drag" !== c && 1 === d.options.items && !0 === d.browser.support3d) return d.swapSpeed(0), !0 === d.browser.support3d ? d.transition3d(d.positionsInArray[a]) : d.css2slide(d.positionsInArray[a], 1), d.afterGo(), d.singleItemTransition(), !1;
			a = d.positionsInArray[a];
			!0 === d.browser.support3d ? (d.isCss3Finish = !1, !0 === b ? (d.swapSpeed("paginationSpeed"), k.setTimeout(function () {
					d.isCss3Finish = !0
				}, d.options.paginationSpeed)) : "rewind" === b ? (d.swapSpeed(d.options.rewindSpeed), k.setTimeout(function () {
					d.isCss3Finish = !0
				}, d.options.rewindSpeed)) : (d.swapSpeed("slideSpeed"), k.setTimeout(function () {
					d.isCss3Finish = !0
				}, d.options.slideSpeed)), d.transition3d(a)) : !0 === b ? d.css2slide(a, d.options.paginationSpeed) : "rewind" === b ? d.css2slide(a, d.options.rewindSpeed) :
				d.css2slide(a, d.options.slideSpeed);
			d.afterGo()
		},
		jumpTo: function (a) {
			"function" === typeof this.options.beforeMove && this.options.beforeMove.apply(this, [this.$elem]);
			a >= this.maximumItem || -1 === a ? a = this.maximumItem : 0 >= a && (a = 0);
			this.swapSpeed(0);
			!0 === this.browser.support3d ? this.transition3d(this.positionsInArray[a]) : this.css2slide(this.positionsInArray[a], 1);
			this.currentItem = this.prod.currentItem = a;
			this.afterGo()
		},
		afterGo: function () {
			var a = this;
			a.prevArr.push(a.currentItem);
			a.prevItem = a.prod.prevItem = a.prevArr[a.prevArr.length -
				2];
			a.prevArr.shift(0);
			if (a.prevItem !== a.currentItem && (a.checkPagination(), a.checkNavigation(), a.eachMoveUpdate(), !1 !== a.options.autoPlay && a.checkAp(), g.global.open)) {
				var b = a.$elem.find(".prod-item.active").index() + 1;
				a.$elem.closest(".rwaltz-open-gallery").find(".ea-current").html(b);
				1 == b ? a.$elem.closest(".rwaltz-open-gallery").find(".rwaltz-prev").addClass("disabled") : b == a.$elem.find(".prod-item").length ? a.$elem.closest(".rwaltz-open-gallery").find(".rwaltz-next").addClass("disabled") : a.$elem.closest(".rwaltz-open-gallery").find(".rwaltz-next, .rwaltz-prev").removeClass("disabled");
				setTimeout(function () {
					a.$prodItems.find(".rwaltz-item").removeAttr("style");
					g.global.open.find(".top-controls").hasClass("ea-hide") || a.$prodItems.find(".title").removeClass("hideTitle").removeClass("hideTitleMobile");
					g.global.leftDirection = !0;
					g.global.rightDirection = !0;
					g.global.topBottomDirection = !1;
					g.global.newX = 0;
					g.global.newY = 0;
					a.$prodItems.find(".rwaltz-item").data("scale", 1)
				}, a.options.slideSpeed)
			}
			"function" === typeof a.options.afterMove && a.prevItem !== a.currentItem && a.options.afterMove.apply(this, [a.$elem])
		},
		stop: function () {
			this.apStatus = "stop";
			k.clearInterval(this.autoPlayInterval)
		},
		checkAp: function () {
			"stop" !== this.apStatus && this.play()
		},
		play: function () {
			var a = this;
			a.apStatus = "play";
			if (!1 === a.options.autoPlay) return !1;
			k.clearInterval(a.autoPlayInterval);
			a.autoPlayInterval = k.setInterval(function () {
				a.next(!0)
			}, a.options.autoPlay)
		},
		swapSpeed: function (a) {
			"slideSpeed" === a ? this.$prodWrapper.css(this.addCssSpeed(this.options.slideSpeed)) : "paginationSpeed" === a ? this.$prodWrapper.css(this.addCssSpeed(this.options.paginationSpeed)) :
				"string" !== typeof a && this.$prodWrapper.css(this.addCssSpeed(a))
		},
		addCssSpeed: function (a) {
			return {
				"-webkit-transition": "all " + a + "ms ease",
				"-moz-transition": "all " + a + "ms ease",
				"-o-transition": "all " + a + "ms ease",
				transition: "all " + a + "ms ease"
			}
		},
		removeTransition: function () {
			return {
				"-webkit-transition": "",
				"-moz-transition": "",
				"-o-transition": "",
				transition: ""
			}
		},
		doTranslate: function (a) {
			return {
				"-webkit-transform": "translate3d(" + a + "px, 0px, 0px)",
				"-moz-transform": "translate3d(" + a + "px, 0px, 0px)",
				"-o-transform": "translate3d(" +
					a + "px, 0px, 0px)",
				"-ms-transform": "translate3d(" + a + "px, 0px, 0px)",
				transform: "translate3d(" + a + "px, 0px,0px)"
			}
		},
		transition3d: function (a) {
			this.$prodWrapper.css(this.doTranslate(a))
		},
		css2move: function (a) {
			this.$prodWrapper.css({
				left: a
			})
		},
		css2slide: function (a, b) {
			var c = this;
			c.isCssFinish = !1;
			c.$prodWrapper.stop(!0, !0).animate({
				left: a
			}, {
				duration: b || c.options.slideSpeed,
				complete: function () {
					c.isCssFinish = !0
				}
			})
		},
		checkBrowser: function () {
			var a = q.createElement("div");
			a.style.cssText = "  -moz-transform:translate3d(0px, 0px, 0px); -ms-transform:translate3d(0px, 0px, 0px); -o-transform:translate3d(0px, 0px, 0px); -webkit-transform:translate3d(0px, 0px, 0px); transform:translate3d(0px, 0px, 0px)";
			a = a.style.cssText.match(/translate3d\(0px, 0px, 0px\)/g);
			this.browser = {
				support3d: null !== a && 1 === a.length,
				isTouch: "ontouchstart" in k || k.navigator.msMaxTouchPoints
			}
		},
		moveEvents: function () {
			if (!1 !== this.options.mouseDrag || !1 !== this.options.touchDrag) this.gestures(), this.disabledEvents()
		},
		eventTypes: function () {
			var a = ["s", "e", "x"];
			this.ev_types = {};
			!0 === this.options.mouseDrag && !0 === this.options.touchDrag ? a = ["touchstart.prod mousedown.prod", "touchmove.prod mousemove.prod", "touchend.prod touchcancel.prod mouseup.prod"] :
				!1 === this.options.mouseDrag && !0 === this.options.touchDrag ? a = ["touchstart.prod", "touchmove.prod", "touchend.prod touchcancel.prod"] : !0 === this.options.mouseDrag && !1 === this.options.touchDrag && (a = ["mousedown.prod", "mousemove.prod", "mouseup.prod"]);
			this.ev_types.start = a[0];
			this.ev_types.move = a[1];
			this.ev_types.end = a[2]
		},
		disabledEvents: function () {
			this.$elem.on("dragstart.prod", function (a) {
				a.preventDefault()
			});
			this.$elem.on("mousedown.disableTextSelect", function (a) {
				return f(a.target).is("input, textarea, select, option")
			})
		},
		gestures: function () {
			function a(a) {
				if (void 0 !== a.touches) return {
					x: a.touches[0].pageX,
					y: a.touches[0].pageY
				};
				if (void 0 === a.touches) {
					if (void 0 !== a.pageX) return {
						x: a.pageX,
						y: a.pageY
					};
					if (void 0 === a.pageX) return {
						x: a.clientX,
						y: a.clientY
					}
				}
			}

			function b(a) {
				"on" === a ? (f(q).on(h.ev_types.move, d), f(q).on(h.ev_types.end, n)) : "off" === a && (f(q).off(h.ev_types.move), f(q).off(h.ev_types.end))
			}

			function c(b) {
				var c = e.prodActive.find(".rwaltz-item"),
					d = e.prodActive.find("img"),
					f = e.prodActive.find(".rwaltz-item").data("scale");
				e.newX =
					a(b).x;
				e.newY = a(b).y;
				d.offset().left > e.prodActive.offset().left && e.newX > e.oldX || d.offset().left + d.width() * f < e.prodActive.offset().left + e.prodActive.width() && e.newX < e.oldX ? g.global.newX += (e.newX - e.oldX) / f / 3 : (g.global.newX += (e.newX - e.oldX) / f, g.global.leftDirection = !1, g.global.rightDirection = !1);
				1 == g.global.topBottomDirection && (d.offset().top > e.prodActive.offset().top && e.newY > e.oldY || d.offset().top + d.height() * f < e.prodActive.offset().top + e.prodActive.height() && e.newY < e.oldY ? g.global.newY += (e.newY - e.oldY) / f /
					3 : g.global.newY += (e.newY - e.oldY) / f);
				g.updateImg(c, f, g.global.newX, g.global.newY);
				e.oldX = e.newX;
				e.oldY = e.newY
			}

			function d(b) {
				b = b.originalEvent || b || k.event;
				if (g.global.isZoom) return !1;
				h.newPosX = a(b).x - e.offsetX;
				h.newPosY = a(b).y - e.offsetY;
				h.newRelativeX = h.newPosX - e.relativePos;
				h.newRelativeY = h.newPosY;
				if (0 < h.newRelativeX && 0 == g.global.leftDirection) {
					c(b);
					if (3 < h.newRelativeX || -3 > h.newRelativeX || 3 < h.newRelativeY || -3 > h.newRelativeY) g.global.isNoDrag = 1;
					return !1
				}
				if (0 > h.newRelativeX && 0 == g.global.rightDirection) {
					c(b);
					if (3 < h.newRelativeX || -3 > h.newRelativeX || 3 < h.newRelativeY || -3 > h.newRelativeY) g.global.isNoDrag = 1;
					return !1
				}
				if (1 == g.global.topBottomDirection) {
					var d = e.prodActive.find(".rwaltz-item"),
						t = e.prodActive.find("img"),
						n = e.prodActive.find(".rwaltz-item").data("scale");
					e.newY = a(b).y;
					t.offset().top > e.prodActive.offset().top && e.newY > e.oldY || t.offset().top + t.height() * n < e.prodActive.offset().top + e.prodActive.height() && e.newY < e.oldY ? g.global.newY += (e.newY - e.oldY) / n / 3 : g.global.newY += (e.newY - e.oldY) / n;
					g.updateImg(d, n, g.global.newX,
						g.global.newY);
					e.oldY = e.newY
				}
				"function" === typeof h.options.startDragging && !0 !== e.dragging && 0 !== h.newRelativeX && (e.dragging = !0, h.options.startDragging.apply(h, [h.$elem]));
				(8 < h.newRelativeX || -8 > h.newRelativeX) && !0 === h.browser.isTouch && (void 0 !== b.preventDefault ? b.preventDefault() : b.returnValue = !1, e.sliding = !0);
				(10 < h.newPosY || -10 > h.newPosY) && !1 === e.sliding && 0 == g.global.topBottomDirection && f(q).off("touchmove.prod");
				h.newPosX = Math.max(Math.min(h.newPosX, h.newRelativeX / 5), h.maximumPixels + h.newRelativeX /
					5);
				!0 === h.browser.support3d ? h.transition3d(h.newPosX) : h.css2move(h.newPosX);
				if (3 < h.newRelativeX || -3 > h.newRelativeX) g.global.isNoDrag = 1;
				g.global.isDrag = 1
			}

			function n(a) {
				a = a.originalEvent || a || k.event;
				var c;
				c = e.prodActive.find(".rwaltz-item").data("scale");
				if (0 < h.newRelativeX && 0 == g.global.leftDirection) {
					var d = e.prodActive.find("img"),
						n = e.prodActive.find(".rwaltz-item");
					a = (d.offset().left - e.prodActive.offset().left) / c;
					0 <= a && (g.global.leftDirection = !0, g.global.newX -= a);
					if (1 == g.global.topBottomDirection) {
						var p =
							(d.offset().top - e.prodActive.offset().top) / c,
							d = (d.offset().top + d.height() * c - (e.prodActive.offset().top + e.prodActive.height())) / c;
						0 < p && (g.global.newY -= p);
						0 > d && (g.global.newY -= d)
					} else g.global.newY = 0;
					if (0 <= a || 1 == g.global.topBottomDirection) n.css(m.addCssSpeed(100)), g.updateImg(n, c, g.global.newX, g.global.newY), setTimeout(function () {
						n.css(m.removeTransition())
					}, 100);
					b("off");
					g.global.isDrag = 0;
					return !1
				}
				if (0 > h.newRelativeX && 0 == g.global.rightDirection) {
					d = e.prodActive.find("img");
					n = e.prodActive.find(".rwaltz-item");
					a = (d.offset().left + d.width() * c - (e.prodActive.offset().left + e.prodActive.width())) / c;
					0 >= a && (g.global.rightDirection = !0, g.global.newX -= a);
					1 == g.global.topBottomDirection ? (p = (d.offset().top - e.prodActive.offset().top) / c, d = (d.offset().top + d.height() * c - (e.prodActive.offset().top + e.prodActive.height())) / c, 0 < p && (g.global.newY -= p), 0 > d && (g.global.newY -= d)) : g.global.newY = 0;
					if (0 >= a || 1 == g.global.topBottomDirection) n.css(m.addCssSpeed(100)), g.updateImg(n, c, g.global.newX, g.global.newY), setTimeout(function () {
							n.css(m.removeTransition())
						},
						100);
					b("off");
					g.global.isDrag = 0;
					return !1
				}
				1 == g.global.topBottomDirection && (d = e.prodActive.find("img"), n = e.prodActive.find(".rwaltz-item"), p = (d.offset().top - e.prodActive.offset().top) / c, d = (d.offset().top + d.height() * c - (e.prodActive.offset().top + e.prodActive.height())) / c, 0 < p && (g.global.newY -= p, n.css(m.addCssSpeed(100)), g.updateImg(n, c, g.global.newX, g.global.newY), setTimeout(function () {
					n.css(m.removeTransition())
				}, 100)), 0 > d && (g.global.newY -= d, n.css(m.addCssSpeed(100)), g.updateImg(n, c, g.global.newX, g.global.newY),
					setTimeout(function () {
						n.css(m.removeTransition())
					}, 100)));
				a.target = a.target || a.srcElement;
				e.dragging = !1;
				!0 !== h.browser.isTouch && h.$prodWrapper.removeClass("grabbing");
				h.dragDirection = 0 > h.newRelativeX ? h.prod.dragDirection = "left" : h.prod.dragDirection = "right";
				0 !== h.newRelativeX && (c = h.getNewPosition(), h.goTo(c, !1, "drag"), e.targetElement === a.target && !0 !== h.browser.isTouch && (f(a.target).on("click.disable", function (a) {
						a.stopImmediatePropagation();
						a.stopPropagation();
						a.preventDefault();
						f(a.target).off("click.disable")
					}),
					a = f._data(a.target, "events").click, c = a.pop(), a.splice(0, 0, c)));
				g.global.isDrag = 0;
				b("off")
			}
			var h = this,
				e = {
					offsetX: 0,
					offsetY: 0,
					baseElWidth: 0,
					relativePos: 0,
					position: null,
					minSwipe: null,
					maxSwipe: null,
					sliding: null,
					dargging: null,
					targetElement: null,
					prodActive: null,
					newX: 0,
					newY: 0,
					oldX: 0,
					oldY: 0
				};
			h.isCssFinish = !0;
			h.$elem.on(h.ev_types.start, ".prod-wrapper", function (c) {
				c = c.originalEvent || c || k.event;
				var d;
				e.prodActive = f(this).find(".prod-item.active");
				if (3 === c.which) return !1;
				if (!(h.itemsAmount <= h.options.items)) {
					if (!1 ===
						h.isCssFinish && !h.options.dragBeforeAnimFinish || !1 === h.isCss3Finish && !h.options.dragBeforeAnimFinish) return !1;
					!1 !== h.options.autoPlay && k.clearInterval(h.autoPlayInterval);
					!0 === h.browser.isTouch || h.$prodWrapper.hasClass("grabbing") || h.$prodWrapper.addClass("grabbing");
					h.newPosX = 0;
					h.newRelativeX = 0;
					f(this).css(h.removeTransition());
					d = f(this).position();
					e.relativePos = d.left;
					e.offsetX = a(c).x - d.left;
					e.offsetY = a(c).y - d.top;
					e.oldX = a(c).x;
					e.oldY = a(c).y;
					b("on");
					e.sliding = !1;
					e.targetElement = c.target || c.srcElement;
					g.global.isNoDrag = 0
				}
			})
		},
		getNewPosition: function () {
			var a = this.closestItem();
			a > this.maximumItem ? a = this.currentItem = this.maximumItem : 0 <= this.newPosX && (this.currentItem = a = 0);
			return a
		},
		closestItem: function () {
			var a = this,
				b = !0 === a.options.scrollPerPage ? a.pagesInArray : a.positionsInArray,
				c = a.newPosX,
				d = null;
			f.each(b, function (g, h) {
				c - a.itemWidth / 20 > b[g + 1] && c - a.itemWidth / 20 < h && "left" === a.moveDirection() ? (d = h, a.currentItem = !0 === a.options.scrollPerPage ? f.inArray(d, a.positionsInArray) : g) : c + a.itemWidth / 20 < h && c + a.itemWidth /
					20 > (b[g + 1] || b[g] - a.itemWidth) && "right" === a.moveDirection() && (!0 === a.options.scrollPerPage ? (d = b[g + 1] || b[b.length - 1], a.currentItem = f.inArray(d, a.positionsInArray)) : (d = b[g + 1], a.currentItem = g + 1))
			});
			return a.currentItem
		},
		moveDirection: function () {
			var a;
			0 > this.newRelativeX ? (a = "right", this.playDirection = "next") : (a = "left", this.playDirection = "prev");
			return a
		},
		customEvents: function () {
			var a = this;
			a.$elem.on("prod.next", function () {
				a.next()
			});
			a.$elem.on("prod.prev", function () {
				a.prev()
			});
			a.$elem.on("prod.play", function (b,
				c) {
				a.options.autoPlay = c;
				a.play();
				a.hoverStatus = "play"
			});
			a.$elem.on("prod.stop", function () {
				a.stop();
				a.hoverStatus = "stop"
			});
			a.$elem.on("prod.goTo", function (b, c) {
				a.goTo(c)
			});
			a.$elem.on("prod.jumpTo", function (b, c) {
				a.jumpTo(c)
			})
		},
		stopOnHover: function () {
			var a = this;
			!0 === a.options.stopOnHover && !0 !== a.browser.isTouch && !1 !== a.options.autoPlay && (a.$elem.on("mouseover", function () {
				a.stop()
			}), a.$elem.on("mouseout", function () {
				"stop" !== a.hoverStatus && a.play()
			}))
		},
		lazyLoad: function () {
			var a, b, c, d, g;
			if (!1 === this.options.lazyLoad) return !1;
			for (a = 0; a < this.itemsAmount; a += 1) b = f(this.$prodItems[a]), "loaded" !== b.data("prod-loaded") && (c = b.data("prod-item"), d = b.find(".lazyprod"), "string" !== typeof d.data("src") ? b.data("prod-loaded", "loaded") : (void 0 === b.data("prod-loaded") && (d.hide(), b.addClass("loading").data("prod-loaded", "checked")), (g = !0 === this.options.lazyFollow ? c >= this.currentItem : !0) && c < this.currentItem + this.options.items && d.length && this.lazyPreload(b, d)))
		},
		lazyPreload: function (a, b) {
			function c() {
				a.data("prod-loaded", "loaded").removeClass("loading");
				b.removeAttr("data-src");
				"fade" === f.options.lazyEffect ? b.fadeIn(400) : b.show();
				"function" === typeof f.options.afterLazyLoad && f.options.afterLazyLoad.apply(this, [f.$elem])
			}

			function d() {
				g += 1;
				f.completeImg(b.get(0)) || !0 === e ? c() : 100 >= g ? k.setTimeout(d, 100) : c()
			}
			var f = this,
				g = 0,
				e;
			"DIV" === b.prop("tagName") ? (b.css("background-image", "url(" + b.data("src") + ")"), e = !0) : b[0].src = b.data("src");
			d()
		},
		autoHeight: function () {
			function a() {
				var a = f(c.$prodItems[c.currentItem]).height();
				c.wrapperOuter.css("height", a + "px");
				c.wrapperOuter.hasClass("autoHeight") ||
					k.setTimeout(function () {
						c.wrapperOuter.addClass("autoHeight")
					}, 0)
			}

			function b() {
				g += 1;
				c.completeImg(d.get(0)) ? a() : 100 >= g ? k.setTimeout(b, 100) : c.wrapperOuter.css("height", "")
			}
			var c = this,
				d = f(c.$prodItems[c.currentItem]).find("img"),
				g;
			void 0 !== d.get(0) ? (g = 0, b()) : a()
		},
		completeImg: function (a) {
			return !a.complete || "undefined" !== typeof a.naturalWidth && 0 === a.naturalWidth ? !1 : !0
		},
		onVisibleItems: function () {
			var a;
			!0 === this.options.addClassActive && this.$prodItems.removeClass("active");
			this.visibleItems = [];
			for (a = this.currentItem; a <
				this.currentItem + this.options.items; a += 1) this.visibleItems.push(a), !0 === this.options.addClassActive && f(this.$prodItems[a]).addClass("active");
			this.prod.visibleItems = this.visibleItems
		},
		transitionTypes: function (a) {
			this.outClass = "prod-" + a + "-out";
			this.inClass = "prod-" + a + "-in"
		},
		singleItemTransition: function () {
			var a = this,
				b = a.outClass,
				c = a.inClass,
				d = a.$prodItems.eq(a.currentItem),
				f = a.$prodItems.eq(a.prevItem),
				g = Math.abs(a.positionsInArray[a.currentItem]) + a.positionsInArray[a.prevItem],
				e = Math.abs(a.positionsInArray[a.currentItem]) +
				a.itemWidth / 2;
			a.isTransition = !0;
			a.$prodWrapper.addClass("prod-origin").css({
				"-webkit-transform-origin": e + "px",
				"-moz-perspective-origin": e + "px",
				"perspective-origin": e + "px"
			});
			f.css({
				position: "relative",
				left: g + "px"
			}).addClass(b).on("webkitAnimationEnd oAnimationEnd MSAnimationEnd animationend", function () {
				a.endPrev = !0;
				f.off("webkitAnimationEnd oAnimationEnd MSAnimationEnd animationend");
				a.clearTransStyle(f, b)
			});
			d.addClass(c).on("webkitAnimationEnd oAnimationEnd MSAnimationEnd animationend", function () {
				a.endCurrent = !0;
				d.off("webkitAnimationEnd oAnimationEnd MSAnimationEnd animationend");
				a.clearTransStyle(d, c)
			})
		},
		clearTransStyle: function (a, b) {
			a.css({
				position: "",
				left: ""
			}).removeClass(b);
			this.endPrev && this.endCurrent && (this.$prodWrapper.removeClass("prod-origin"), this.isTransition = this.endCurrent = this.endPrev = !1)
		},
		prodStatus: function () {
			this.prod = {
				userOptions: this.userOptions,
				baseElement: this.$elem,
				userItems: this.$userItems,
				prodItems: this.$prodItems,
				currentItem: this.currentItem,
				prevItem: this.prevItem,
				visibleItems: this.visibleItems,
				isTouch: this.browser.isTouch,
				browser: this.browser,
				dragDirection: this.dragDirection
			}
		},
		clearEvents: function () {
			this.$elem.off(".prod prod mousedown.disableTextSelect");
			f(q).off(".prod prod");
			f(k).off("resize", this.resizer)
		},
		unWrap: function () {
			0 !== this.$elem.children().length && (this.$prodWrapper.unwrap(), this.$userItems.unwrap().unwrap(), this.prodControls && this.prodControls.remove());
			this.clearEvents();
			this.$elem.attr("style", this.$elem.data("prod-originalStyles") || "").attr("class", this.$elem.data("prod-originalClasses"))
		},
		destroy: function () {
			this.stop();
			k.clearInterval(this.checkVisible);
			this.unWrap();
			this.$elem.removeData()
		},
		reinit: function (a) {
			a = f.extend({}, this.userOptions, a);
			this.unWrap();
			this.init(a, this.$elem)
		},
		addItem: function (a, b) {
			var c;
			if (!a) return !1;
			if (0 === this.$elem.children().length) return this.$elem.append(a), this.setVars(), !1;
			this.unWrap();
			c = void 0 === b || -1 === b ? -1 : b;
			c >= this.$userItems.length || -1 === c ? this.$userItems.eq(-1).after(a) : this.$userItems.eq(c).before(a);
			this.setVars()
		},
		removeItem: function (a) {
			if (0 ===
				this.$elem.children().length) return !1;
			a = void 0 === a || -1 === a ? -1 : a;
			this.unWrap();
			this.$userItems.eq(a).remove();
			this.setVars()
		}
	};
	f.fn.prodCarouselE = function (a) {
		return this.each(function () {
			if (!0 === f(this).data("prod-init")) return !1;
			f(this).data("prod-init", !0);
			var b = Object.create(m);
			b.init(a, this);
			f.data(this, "prodCarouselE", b)
		})
	};
	f.fn.prodCarouselE.options = {
		items: 5,
		itemsCustom: !1,
		itemsDesktop: [1199, 4],
		itemsDesktopSmall: [979, 3],
		itemsTablet: [768, 2],
		itemsTabletSmall: !1,
		itemsMobile: [479, 1],
		singleItem: !1,
		itemsScaleUp: !1,
		slideSpeed: 200,
		paginationSpeed: 800,
		rewindSpeed: 1E3,
		autoPlay: !1,
		stopOnHover: !1,
		navigation: !1,
		navigationText: ["prev", "next"],
		rewindNav: !0,
		scrollPerPage: !1,
		pagination: !0,
		paginationNumbers: !1,
		responsive: !0,
		responsiveRefreshRate: 200,
		responsiveBaseWidth: k,
		baseClass: "prod-carousel",
		theme: "prod-theme",
		lazyLoad: !1,
		lazyFollow: !0,
		lazyEffect: "fade",
		autoHeight: !1,
		jsonPath: !1,
		jsonSuccess: !1,
		dragBeforeAnimFinish: !0,
		mouseDrag: !0,
		touchDrag: !0,
		addClassActive: !1,
		transitionStyle: !1,
		beforeUpdate: !1,
		afterUpdate: !1,
		beforeInit: !1,
		afterInit: !1,
		beforeMove: !1,
		afterMove: !1,
		afterAction: !1,
		startDragging: !1,
		afterLazyLoad: !1
	}
})(jQuery, window, document);