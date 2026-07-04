(function(window){
	'use strict';

	if(window.JCCatalogElement)
		return;

	window.JCCatalogElement = function(arParams)
	{
		this.visual = arParams.VISUAL || {};

		this.product = arParams.PRODUCT || {};

		this.offers = arParams.OFFERS || [];
		this.offerNum = parseInt(arParams.OFFER_SELECTED) || 0;

		this.targetTabId = 'shops-availability';
		this.selectedTabId = null;
		this.shopsAvailabilityLoaded = null;
		this.shopsAvailabilityContent = {};

		this.loading = BX.create('DIV', {
			props: {
				className: 'loading loading--block'
			},
			children: [
				BX.create('DIV', {
					props: {
						className: 'loading__content'
					},
					children: [
						BX.create('DIV', {
							props: {
								className: 'loading__icon'
							}
						})
					]
				})
			]
		});

		this.popupForm = null;

		BX.ready(BX.delegate(this.init, this));
	}

	window.JCCatalogElement.prototype = {
		init: function()
		{
			this.obProduct = document.getElementById(this.visual.ID);

			this.btnSizeTable = this.obProduct.querySelector('.popup-link__size-table');

			$('[data-fancybox="gallery"]').fancybox({
				backFocus: false
			});

			this.initSlider();

			if(this.offers.length > 0) {
				var url = new URL(window.location.href);
				var offerId = url.searchParams.get('oid');
				if(offerId) {
					this.setTargetOffer(offerId);
				} else {
					this.setOffer(false);
				}

				var sizes = this.obProduct.querySelectorAll('.product-sizes__item');
				sizes.forEach((size) => {
					var sizeText = size.querySelector('.radio-text');
					sizeText.addEventListener('click', () => {
						var input = event.target.parentNode.querySelector('input[type="radio"]');
						if(!input.checked) {
							this.changeOffer(input);
						}
					});
				});
			}

			if(!!this.btnSizeTable) {
				this.btnSizeTable.addEventListener('click', () => this.getSizeTable());
			}

			this.checkShopsAvailabilityTab();

			document.addEventListener('tabAfterChange', () => this.tabAfterChange());

			document.addEventListener('offerSelected', () => {

				if(!event.detail.available) {
					return;
				}

				var index = this.offers.findIndex(offer => {
					return +offer.ID === +event.detail.id
				});

				if(!(index > -1)) {
					return;
				}

				for(let inputName in event.detail.tree) {
					const value = event.detail.tree[inputName];
					const input = document.querySelector('input[name="'+inputName+'"][value="'+value+'"]');
					if(input) {
						input.checked = true;
					}
				}

				this.offerNum = index;
				this.setPrice();
				this.setCart();
				this.setProps();

				this.checkShopsAvailabilityTab();

			});

			document.addEventListener('openSizeTable', () => this.getSizeTable())
		},

		initSlider: function()
		{
			new Swiper('.product-card__slider', {
				loop: false,
				slidesPerView: 1,
				spaceBetween: 23,
				speed: 1000,
				navigation: {
					nextEl: '.product-card__slider-arrow--next',
					prevEl: '.product-card__slider-arrow--prev',
				},
				breakpoints: {
					576: {
						slidesPerView: 2,
					},
					768: {
						slidesPerView: 2,
					},
					1025: {
						slidesPerView: 2,
						spaceBetween: 52,
					}
				}
			});
		},

		setOffer: function(userSelect = true)
		{
			this.offers.forEach((offer, i) => {
				Object.keys(offer.TREE).forEach((key) => {
					var input = this.obProduct.querySelector('[name="' + key + '"][value="'+ offer.TREE[key] +'"]');

					if(userSelect && i === this.offerNum) {
						input.checked = true;
					}

					if(!offer.CAN_BUY) {
						input.disabled = true;
					}
				});
			});
		},

		setTargetOffer: function(offerId)
		{
			var index = this.offers.findIndex(offer => {
				return +offer.ID === +offerId
			});

			if(index > -1) {
				this.offerNum = index;

				this.setOffer(false);

				this.setPrice();
				this.setProps();
			} else {
				this.setOffer(true);
			}
		},

		changeOffer: function(input)
		{
			var index = this.offers.findIndex(offer => {
				return +offer.TREE[input.name] === +input.value
			});

			if(index > -1) {
				if(!input.disabled) {
					this.offerNum = index;

					this.setPrice();

					this.setCart();

					this.setProps();

					this.checkShopsAvailabilityTab();
				} else {
					this.getSubscribe(index);
				}
			}
		},

		setPrice: function()
		{
			var currentOffer = this.offers[this.offerNum],
				currentPrice = currentOffer.OPTIMAL_PRICE;

			var priceDiv = this.obProduct.querySelector('.new-price');
			priceDiv.innerHTML = currentPrice.PRINT_PRICE;

			var oldPriceDiv = this.obProduct.querySelector('.old-price');
			if(currentPrice.PERCENT > 0) {
				oldPriceDiv.innerHTML = currentPrice.PRINT_BASE_PRICE;
				oldPriceDiv.style.display = '';
			} else {
				oldPriceDiv.innerHTML = '';
				oldPriceDiv.style.display = 'none';
			}

			var labelDiv = this.obProduct.querySelector('.label');
			if(currentPrice.PERCENT > 0) {
				labelDiv.innerHTML = currentPrice.PERCENT + '%';
				labelDiv.style.display = '';
			} else {
				labelDiv.innerHTML = '';
				labelDiv.style.display = 'none';
			}
		},

		setCart: function()
		{
			var cartButtons = this.obProduct.querySelectorAll('.product-cart-btn');

			cartButtons.forEach((cartButton) => {
				if(cartButton.getAttribute('data-id') === this.offers[this.offerNum].ID) {
					cartButton.classList.remove('product-cart-btn__hidden');
				} else {
					cartButton.classList.add('product-cart-btn__hidden');
				}
			});
		},

		setProps: function()
		{
			for(var i in this.offers[this.offerNum].PROPERTIES) {
				var offerProp = this.offers[this.offerNum].PROPERTIES[i],
					productProp = this.obProduct.querySelector('[data-prop="' + offerProp.ID + '"]');

				if(!!productProp) {
					productProp.innerText = Array.isArray(offerProp.DISPLAY_VALUE) ? offerProp.DISPLAY_VALUE.join(' / ') : offerProp.DISPLAY_VALUE;
				}
			}
		},

		getSubscribe: async function(offerNum)
		{
			var popupContent = BX.create('DIV', {
				props: {
					className: 'mini-popup__content'
				},
				children: [
					BX.create('DIV', {
						props: {
							className: 'mini-popup__title'
						},
						html: BX.message('SUBSCRIBE_TITLE')
					}),
					BX.create('DIV', {
						props: {
							className: 'mini-popup__subtitle'
						},
						html: BX.message('SUBSCRIBE_SUBTITLE')
					}),
					this.loading
				]
			});

			var popup = BX.create('DIV', {
				props: {
					className: 'popup mini-popup'
				},
				children: [
					BX.create('DIV', {
						props: {
							className: 'mini-popup__inner'
						},
						children: [
							BX.create('DIV', {
								props: {
									className: 'mini-popup__img'
								},
								children: [
									BX.create('IMG', {
										props: {
											className: 'lazy',
											src: '/local/templates/main/assets/images/content/popups/size-subscribe.jpg',
											alt: ''
										}
									})
								]
							}),
							popupContent
						]
					})
				]
			});

			$.fancybox.open(popup);

			try {
				const response = await BX.ajax.runAction('imedia:main.subscribeproduct.get', {
					method: 'get',
					getParameters: {
						productId: this.offers[offerNum].ID
					}
				});

				if(!response.data.IS_SUBSCRIBED) {
					this.popupForm = BX.create('FORM', {
						props: {
							className: 'subscribe-form',
							method: 'POST'
						},
						children: [
							BX.create('DIV', {
								props: {
									className: 'form-row subscribe-form__wrap'
								},
								children: [
									BX.create('INPUT', {
										props: {
											className: 'form-row__input',
											type: 'email',
											name: 'email',
											placeholder: BX.message('SUBSCRIBE_EMAIL'),
											required: true
										}
									}),
									BX.create('BUTTON', {
										props: {
											className: 'btn form-row__btn',
											type: 'submit'
										},
										attrs: {
											'aria-label': BX.message('SUBSCRIBE_BTN')
										},
										events: {
											click: () => this.subscribe(response.data.ID)
										}
									})
								]
							}),
							BX.create('LABEL', {
								props: {
									className: 'form-agree'
								},
								children: [
									BX.create('INPUT', {
										props: {
											className: 'check-box',
											type: 'checkbox',
											name: 'user_agree',
											required: true
										}
									}),
									BX.create('SPAN', {
										props: {
											className: 'check-style'
										}
									}),
									BX.create('SPAN', {
										props: {
											className: 'check-text'
										},
										html: BX.message('SUBSCRIBE_AGREEMENT')
									})
								]
							})
						]
					});
				} else {
					this.popupForm = BX.create('DIV', {
						props: {
							className: 'form-success subscribe-form__success'
						},
						html: BX.message('ALREADY_SUBSCRIBED')
					});
				}

				popupContent.removeChild(this.loading);
				popupContent.appendChild(this.popupForm);
			} catch(e) {
				console.log(e);
			}
		},

		subscribe: async function(productId)
		{
			var isFormValid = this.popupForm.checkValidity();

			if(!isFormValid) {
				this.popupForm.reportValidity();
			} else {
				event.preventDefault();

				try {
					const data = new FormData();
					data.append('productId', productId);
					data.append('email', this.popupForm.querySelector('[name="email"]').value);

					await BX.ajax.runAction('imedia:main.subscribeproduct.subscribe', {
						data: data
					});

					this.popupForm.innerHTML = '<div class="form-success subscribe-form__success">' + BX.message('SUBSCRIBE_SUCCESS') + '</div>';
				} catch(e) {
					console.log(e);
				}
			}
		},

		getSizeTable: async function()
		{
			var popupInner = BX.create('DIV', {
				props: {
					className: 'product-sizes-popup__inner'
				},
				children: [
					this.loading
				]
			});

			var popup = BX.create('DIV', {
				props: {
					id: 'product-sizes-popup',
					className: 'popup product-sizes-popup'
				},
				children: [
					popupInner
				]
			});

			$.fancybox.open(popup);

			try {
				const response = await BX.ajax.runAction('imedia:main.sizetable.get', {
					method: 'get',
					getParameters: {
						productId: this.product.ID,
						elementId: this.product.SIZE_TABLE > 0 ? this.product.SIZE_TABLE : null,
						sectionIds: this.product.SECTION_IDS.length > 0 ? this.product.SECTION_IDS : null
					}
				});

				var item = response.data,
					brand = item.BRAND,
					popupLogo, popupSubtitle,
					popupPreviewText, popupDetailText;

				if(!!brand && Object.keys(brand).length > 0 && Object.keys(brand.PICTURE).length > 0) {
					popupLogo = BX.create('DIV', {
						props: {
							className: 'product-sizes-popup__logo'
						},
						children: [
							BX.create('IMG', {
								props: {
									className: 'lazy',
									src: brand.PICTURE.RESIZE[0].SIZES.DEFAULT,
									alt: brand.NAME
								}
							})
						]
					});
				}

				if(!!item.NAME && item.NAME.length) {
					popupSubtitle = BX.create('DIV', {
						props: {
							className: 'product-sizes-popup__subtitle'
						},
						html: item.NAME
					});
				}

				popupInner.removeChild(this.loading);

				popupInner.appendChild(BX.create('DIV', {
					props: {
						className: 'product-sizes-popup__top'
					},
					children: [
						popupLogo,
						BX.create('H4', {
							props: {
								className: 'product-sizes-popup__title'
							},
							html: BX.message('TABLE_SIZES')
						}),
						popupSubtitle
					]
				}));

				if(!!item.PREVIEW_TEXT && item.PREVIEW_TEXT.length) {
					popupPreviewText = BX.create('DIV', {
						html: item.PREVIEW_TEXT
					});
					popupPreviewText.childNodes.forEach(element => popupInner.appendChild(element));
				}

				if(!!item.DETAIL_TEXT && item.DETAIL_TEXT.length) {
					popupDetailText = BX.create('DIV', {
						html: item.DETAIL_TEXT
					});
					popupDetailText.childNodes.forEach(element => popupInner.appendChild(element));
				}
			} catch(e) {
				console.log(e);
			}
		},

		checkShopsAvailabilityTab: async function()
		{
			var shopsTab = this.obProduct.querySelector('[href="#' + this.targetTabId + '"]');

			try {
				var productId = this.offers.length > 0 ? this.offers[this.offerNum].ID : this.product.ID;

				const response = await BX.ajax.runAction('imedia:main.shopsavailability.get', {
					method: 'get',
					getParameters: {
						productId: productId
					}
				});

				if(Object.keys(response.data).length) {
					this.shopsAvailabilityContent = response.data;

					shopsTab.style.display = '';

					if(this.selectedTabId === this.targetTabId) {
						this.getShopsAvailability();
					}
				} else {
					shopsTab.style.display = 'none';

					if(this.selectedTabId === this.targetTabId) {
						shopsTab.classList.remove('tab--active');

						var shopsContainer = this.obProduct.querySelector('#' + this.targetTabId);
						shopsContainer.classList.remove('tabs-content--active');
						shopsContainer.innerHTML = '';

						var tabs = this.obProduct.querySelectorAll('.tab');
						tabs[0].classList.add('tab--active');

						var tabsContent = this.obProduct.querySelectorAll('.tabs-content');
						tabsContent[0].classList.add('tabs-content--active');
					}
				}
			} catch(e) {
				console.log(e);
			}
		},

		tabAfterChange: function()
		{
			if(!event.detail.parent.classList.contains('product-card__tabs')) {
				return false;
			}

			this.selectedTabId = event.detail.id;

			if(!(this.selectedTabId === this.targetTabId)) {
				return false;
			}

			var productId = this.offers.length > 0 ? this.offers[this.offerNum].ID : this.product.ID;

			if(this.shopsAvailabilityLoaded === productId) {
				return false;
			}

			this.shopsAvailabilityLoaded = productId;

			this.getShopsAvailability();
		},

		getShopsAvailability: function()
		{
			var shopsContainer = this.obProduct.querySelector('#' + this.targetTabId),
				items = this.shopsAvailabilityContent.ITEMS,
				sections = this.shopsAvailabilityContent.SECTIONS,
				sectionShops;

			shopsContainer.innerHTML = '';

			if(sections.length > 0) {
				var select = BX.create('DIV', {
					props: {
						className: 'select shops-availability__select'
					}
				});

				var selectTitle = BX.create('DIV', {
					props: {
						className: 'select__title'
					},
					html: sections[0].NAME,
					events: {
						click: function() {
							this.classList.toggle('select__title--active');
							$(selectContent).slideToggle('300');
						}
					}
				});

				var selectContent = BX.create('DIV', {
					props: {
						className: 'select__content'
					}
				});

				var content = BX.create('DIV', {
					props: {
						className: 'shops-availability__content'
					},
					style: {
						display: 'block'
					}
				});

				sections.forEach((section, i) => {
					selectContent.appendChild(BX.create('LABEL', {
						props: {
							className: 'select__option'
						},
						children: [
							BX.create('INPUT', {
								props: {
									className: 'select__input',
									type: 'radio',
									name: 'shops-city',
									checked: i === 0
								}
							}),
							BX.create('SPAN', {
								props: {
									className: 'radio-style'
								}
							}),
							BX.create('SPAN', {
								props: {
									className: 'select__option-text'
								},
								html: section.NAME
							})
						],
						events: {
							click: BX.delegate(function() {
								var target = BX.proxy_context;

								selectTitle.innerHTML = target.querySelector('.select__option-text').innerHTML;
								selectTitle.classList.remove('select__title--active');

								$(selectContent).slideUp('300');

								content.innerHTML = '';
								sectionShops = this.getSectionShops(section.ID, items);
								if(sectionShops.children.length > 0) {
									content.appendChild(sectionShops);
								}
							}, this)
						}
					}));
				});

				select.appendChild(selectTitle);
				select.appendChild(selectContent);

				shopsContainer.appendChild(select);

				sectionShops = this.getSectionShops(sections[0].ID, items);
				if(sectionShops.children.length > 0) {
					content.appendChild(sectionShops);
				}

				shopsContainer.appendChild(content);
			}
		},

		getSectionShops: function(sectionId, items)
		{
			var list = BX.create('UL', {
				props: {
					className: 'shops-availability__list'
				}
			});

			items.forEach((item) => {
				if(item.IBLOCK_SECTION_ID === sectionId) {
					var iconSvg = document.createElementNS(
						'http://www.w3.org/2000/svg',
						'svg'
					);
					iconSvg.setAttribute('fill', 'none');
					iconSvg.setAttribute('viewBox', '0 0 32 32');

					var iconPath = document.createElementNS(
						'http://www.w3.org/2000/svg',
						'path'
					);
					iconPath.setAttribute(
						'd',
						'M16 0C9.33809 0 3.9375 5.40056 3.9375 12.0625C3.9375 14.2904 4.37358 16.5915 5.62499 18.25L16 32L26.375 18.25C27.5117 16.7436 28.0625 14.0805 28.0625 12.0625C28.0625 5.40056 22.6619 0 16 0Z'
					);
					iconPath.setAttribute('fill', '#101112');

					var iconEllipse = document.createElementNS(
						'http://www.w3.org/2000/svg',
						'ellipse'
					);
					iconEllipse.setAttribute('cx', '16.0002');
					iconEllipse.setAttribute('cy', '12.3078');
					iconEllipse.setAttribute('rx', '4.92308');
					iconEllipse.setAttribute('ry', '4.92308');
					iconEllipse.setAttribute('fill', 'white');

					iconSvg.appendChild(iconPath);
					iconSvg.appendChild(iconEllipse);

					var address,
						phone;

					if(item.ADDRESS && item.ADDRESS.length) {
						address = BX.create('DIV', {
							props: {
								className: 'shop-item__address'
							},
							html: '<p>' + item.ADDRESS + '</p>' + (item.WORKING_HOURS && item.WORKING_HOURS.length ? '<p>' + item.WORKING_HOURS + '</p>' : '')
						});
					}

					if(item.PHONE && item.PHONE.length) {
						phone = BX.create('A', {
							props: {
								className: 'shop-item__phone',
								href: 'tel:' + item.PHONE.replace(/[^\d\+]/g, '')
							},
							html: '<svg viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">' +
								'<path d="M8.8963 9.39752L9.8763 8.41752C10.0083 8.28716 10.1753 8.19793 10.357 8.16065C10.5387 8.12337 10.7274 8.13965 10.9 8.20752L12.0944 8.68439C12.2689 8.75521 12.4185 8.8761 12.5244 9.03181C12.6303 9.18753 12.6877 9.3711 12.6894 9.55939V11.7469C12.6884 11.875 12.6615 12.0015 12.6103 12.119C12.559 12.2364 12.4846 12.3422 12.3914 12.4301C12.2982 12.518 12.1882 12.5861 12.068 12.6303C11.9478 12.6746 11.8198 12.694 11.6919 12.6875C3.32255 12.1669 1.6338 5.07939 1.31442 2.36689C1.29959 2.23369 1.31314 2.09886 1.35417 1.97127C1.39519 1.84367 1.46277 1.72621 1.55245 1.62661C1.64213 1.52702 1.75188 1.44753 1.87449 1.3934C1.99709 1.33926 2.12977 1.3117 2.2638 1.31252H4.37692C4.56549 1.31308 4.74957 1.37003 4.90551 1.47607C5.06144 1.5821 5.18208 1.73236 5.25192 1.90752L5.7288 3.10189C5.79891 3.27386 5.81679 3.46267 5.78022 3.64475C5.74365 3.82682 5.65425 3.99408 5.52317 4.12564L4.54317 5.10564C4.54317 5.10564 5.10755 8.92502 8.8963 9.39752Z" fill="#101112"></path>' +
								'</svg>' +
								item.PHONE
						});
					}

					list.appendChild(BX.create('LI', {
						props: {
							className: 'shop-item'
						},
						children: [
							BX.create('DIV', {
								props: {
									className: 'shop-item__top'
								},
								children: [
									iconSvg,
									BX.create('DIV', {
										props: {
											className: 'shop-item__title'
										},
										html: item.NAME
									})
								]
							}),
							address,
							phone,
							BX.create('DIV', {
								props: {
									className: 'shop-item__status'
								},
								html: BX.message('SHOPS_AVAILABILITY_IN_STOCK')
							})
						]
					}));
				}
			});

			return list;
		}
	}
})(window);