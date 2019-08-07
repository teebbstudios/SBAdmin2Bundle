(function ($) {
    "use strict"; // Start of use strict
    var urlUtil = {
        /**
         * Url分析
         * @param {String} url 要分析的URL
         * @return {Object} 返回包含'url', 'scheme', 'slash', 'host', 'port', 'path', 'query', 'hash'的数组
         */
        analyseUrl: function (url) {
            var parse_url = /^(?:([A-Za-z]+):)?(\/{0,3})([0-9.\-A-Za-z]+)(?::(\d+))?(?:\/([^?#]*))?(?:\?([^#]*))?(?:#(.*))?$/;
            var analyseResult = parse_url.exec(url);
            var fields = ['url', 'scheme', 'slash', 'host', 'port', 'path', 'query', 'hash'];
            var result = new Object();
            $.each(fields, function (n, item) {
                result[item] = analyseResult[n];
            });
            return result;
        },

        /**
         * 查询/判断url是否有某个参数,如果有该参数，返回参数的值；没有返回null
         * @param {String} url 要查询的url
         * @param {String} name 要查询的参数名
         * @return {String} 参数的值
         */
        hasParameter: function (url, name) {
            var urlAnalyse = this.analyseUrl(url);
            var urlParam = urlAnalyse.query;
            if (typeof (urlParam) != 'undefined') {
                var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
                var r = urlParam.match(reg);
                if (r != null) {
                    return unescape(r[2]);
                }
                //有该参数，但是值为null
                return "";
            }
            //没有该参数
            return null;
        },
        /**
         * url增加参数
         * @param {String} url 要修改的url
         * @param {String} name 要增加的参数名
         * @param {String} value 对应的参数值
         * @return {String} 修改后的结果
         */
        addParameter: function (url, name, value) {
            var newUrl = url;
            var paremeter = name + "=" + value;
            if (url.match("[\?]")) {
                //存在其他参数，用&连接
                newUrl = url + "&" + paremeter;
            } else {
                //没有参数，用?连接
                newUrl = url + "?" + paremeter;
            }
            return newUrl;
        },

        /**
         * 替换Url参数
         * @param {String} url 要修改的Url
         * @param {String} name 要修改的参数名
         * @param {String} value 对应的参数的值
         * @return {String} 修改后的Url
         */
        replaceParameter: function (url, name, value) {
            var newUrl = url;
            if (this.hasParameter(url, name)) {
                //有该参数，修改
                var replacedPar = eval('/(' + name + '=)([^&]*)/gi');
                newUrl = url.replace(replacedPar, name + '=' + value);
            } else {
                //没有该参数，增加
                newUrl = this.addParameter(url, name, value);
            }
            return newUrl;
        },

    };

    // Toggle the side navigation
    $("#sidebarToggle, #sidebarToggleTop").on('click', function (e) {
        $("body").toggleClass("sidebar-toggled");
        var $sidebarAnchor = $(".sidebar");
        $sidebarAnchor.toggleClass("toggled");
        if ($sidebarAnchor.hasClass("toggled")) {
            $('.sidebar .collapse').collapse('hide');
        }

    });

    // Close any open menu accordions when window is resized below 768px
    $(window).resize(function () {
        if ($(window).width() < 768) {
            $('.sidebar .collapse').collapse('hide');
        }

    });

    // Prevent the content wrapper from scrolling when the fixed side navigation hovered over
    $('body.fixed-nav .sidebar').on('mousewheel DOMMouseScroll wheel', function (e) {
        if ($(window).width() > 768) {
            var e0 = e.originalEvent,
                delta = e0.wheelDelta || -e0.detail;
            this.scrollTop += (delta < 0 ? 1 : -1) * 30;
            e.preventDefault();
        }
    });

    // Scroll to top button appear
    $(document).on('scroll', function () {
        var scrollDistance = $(this).scrollTop();
        if (scrollDistance > 100) {
            $('.scroll-to-top').fadeIn();
        } else {
            $('.scroll-to-top').fadeOut();
        }
    });

    // Smooth scrolling using jQuery easing
    $(document).on('click', 'a.scroll-to-top', function (e) {
        var $anchor = $(this);
        $('html, body').stop().animate({
            scrollTop: ($($anchor.attr('href')).offset().top)
        }, 1000, 'easeInOutExpo');
        e.preventDefault();
    });


    // Card Table中全选所有行
    $(document).on('click', '.card table thead input[type=checkbox].check-all', function (e) {
        var $anchor = $(this);
        var $checkboxes = $anchor.closest('table').find('tbody tr td input[type=checkbox]');
        $.each($checkboxes, function (index, value) {
            $(value).prop("checked", !$(value).prop("checked"));
        });
    });

    // Sweetalert2 提醒删除内容警告
    $(document).on('click', 'a.btn-delete-content', function (e) {
        e.preventDefault();
        e.stopPropagation();

        Swal.fire({
            title: '您将要删除内容!',
            text: "删除操作不可恢复，确定要删除吗？",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: '删除',
            cancelButtonText: '取消'
        }).then(function (result) {
            if (result.value) {

                /**
                 * Todo: 此处要使用ajax判断alias是否唯一，如果不唯一则替换为服务器返回的唯一alias
                 */

                Swal.fire({
                    title: '删除成功!',
                    text: '内容已经删除成功.',
                    type: 'success',
                    confirmButtonText: '确定'
                })
            }
        })
    });

    // 机器别名的自动生成
    $(document).on('input', 'input[type=text].transliterate', function (e) {
        var inputValue = $(this).val();
        var $parentAnchor = $(this).closest("div.form-row");

        var alias = slugify(inputValue).replace(/-/ig, '_');

        /**
         * Todo: 此处要使用ajax判断alias是否唯一，如果不唯一则替换为服务器返回的唯一alias
         */


        $parentAnchor.find("span.text-alias").text(alias);

        $parentAnchor.find("input[type=hidden].input-alias").val(alias);

        $parentAnchor.find("div.form-alias").removeClass('d-none');

    });

    // 编辑机器别名
    $(document).on('click', 'a.js-modify-alias', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var $parentAnchor = $(this).closest('div.form-alias');

        $parentAnchor.removeClass('form-inline');

        $parentAnchor.find('div.text-alias-wrapper').addClass('d-none');
        $parentAnchor.find('div.input-alias-wrapper').removeClass('d-none');
        $parentAnchor.find('input[type=hidden].input-alias').attr('type', 'text');
        $parentAnchor.find('div.small.text-muted').removeClass('d-none');

    });


    // 选择字段类型 Select change事件
    $(document).on('change', 'div.select-field-form select', function (e) {
        var $parentAnchor = $(this).closest('div.form-row');
        var $divWrapper = $parentAnchor.closest('div.select-field-form');

        $divWrapper.find('div.field-info').removeClass('d-none');
    });

    // 编辑字段设置，选择字段的数量
    $(document).on('change', 'select#select_field_limit', function (e) {
        var $parentAnchor = $(this).closest('div.select-field-limit');

        if ($(this).val() === '-1') {
            $parentAnchor.find('input[type=number].input-field-limit').attr("disabled", 'disabled');
            $parentAnchor.find('input[type=number].input-field-limit').addClass('d-none');
        }
        if ($(this).val() === 'limit') {
            $parentAnchor.find('input[type=number].input-field-limit').removeAttr('disabled');
            $parentAnchor.find('input[type=number].input-field-limit').removeClass('d-none');
        }
    });


    // Sweetalert2 提醒删除字段警告
    $(document).on('click', 'a.btn-delete-field', function (e) {
        e.preventDefault();
        e.stopPropagation();

        Swal.fire({
            title: '您将要删除字段!',
            text: "删除后该字段保存的内容都将删除。删除操作不可恢复，确定要删除吗？",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: '删除',
            cancelButtonText: '取消'
        }).then(function (result) {
            if (result.value) {

                /**
                 * Todo: 此处要使用ajax判断alias是否唯一，如果不唯一则替换为服务器返回的唯一alias
                 */

                Swal.fire({
                    title: '删除成功!',
                    text: '字段已经删除成功.',
                    type: 'success',
                    confirmButtonText: '确定'
                })
            }
        })
    });

    //允许的扩展名分割符统一替换成小写逗号（,）
    $(document).on('input', 'input[type=text].input-allow-extension-name', function (e) {
        var $inputValue = $(this).val();

        var relacedValue = $inputValue.replace(/[，| |\-|_|\－]/, ',');

        $(this).val(relacedValue);
    });

    //取消a.js-modify-field-weight a.js-modify-term默认行为
    $(document).on('click', 'a.js-sortable', function (e) {
        e.preventDefault();
        e.stopPropagation();
    });

    //Bootstrap Select 搜索框样式修改
    $(document).ready(function () {
        $('.bootstrap-select .bs-searchbox input').addClass('form-control-sm');
    });

    //编辑菜单项页面 左侧添加菜单项 tab 样式修改
    $('.js-add-menu-item a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        var $newActiveTab = $(e.target); // newly activated tab
        var $previousActiveTab = $(e.relatedTarget); // previous active tab

        $newActiveTab.addClass('bg-gray-100 border-bottom-gray-100');

        $previousActiveTab.removeClass('bg-gray-100 border-bottom-gray-100');
    });

    //编辑菜单项页面，展开收缩菜单项，点击事件
    $('.dd-item .accordion-arrow').on('click', function (e) {
        var $collapse = $(e.target).closest('.dd-item').find('.collapse');
        $collapse.collapse('toggle');
    });

    //评论列表页评论操作列表显示
    $('td.js-comment-td').hover(function (e) {
        var $tdEl = $(e.target);
        $tdEl.find('.comment-option').css('left', '0px');
    }, function (e) {
        var $tdEl = $(e.target);
        $tdEl.find('.comment-option').css('left', '-9999em');
    });

    //编辑评论页面修改提交时间事件
    $('button#edit_comment_time_btn').on('click', function (e) {
        var $timeInput = $(e.target).closest('.comment-time').find('input#comment_time');
        console.log($timeInput);
        $timeInput.removeAttr('disabled');
        $(e.target).hide();
    });

    //列表页面 Limit 事件
    $('#select_page_limit').change(function () {
        var $limit = $(this).val();
        var $oldUrl = window.location.href;

        var $newUrl = urlUtil.replaceParameter($oldUrl, 'limit', $limit);

        window.location.replace($newUrl);
    });
})(jQuery); // End of use strict
