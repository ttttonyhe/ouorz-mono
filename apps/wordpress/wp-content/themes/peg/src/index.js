$(document).ready(function () { //避免爆代码

    var now = 20;
    var click = 0; //初始化加载次数
    var paged = 1; //获取当前页数
    var pre_post_id = null;
    var pre_post_con = '';

    /* 展现内容(避免爆代码) */
    $('.article-list').css('opacity', '1');
    $('#header-div').css('opacity', '1');
    $('.cat-real').attr('style', 'display:inline-block');
    /* 展现内容(避免爆代码) */

    new Vue({ //axios获取顶部信息
        el: '#grid-cell',
        data() {
            return {
                posts: null,
                cates: null,
                tages: null,
                loading: true, //v-if判断显示占位符
                loading_cates: true,
                loading_tages: true,
                errored: true,
                loading_css: '',
                version: '',
                notice: {
                    visible: false
                },
                flag: false,
                word: {},
                word_chinese: {
                    cate: {
                        a: '伙伴',
                        b: '推荐',
                        c: '项目',
                        d: '琐碎',
                        e: '折腾',
                        f: '音乐'
                    },
                    list: {
                        cate: '研究学习',
                        tag: '文章标签',
                        cate_tag: '技术',
                        empty: '空',
                        view: '全文速览',
                        view_close: '收起预览',
                        status: '状态',
                        status_empty: '无法克说'
                    },
                },
                word_english: {
                    cate: {
                        a: 'Friends',
                        b: 'Collection',
                        c: 'Projects',
                        d: 'Daily',
                        e: 'Coding',
                        f: 'Music'
                    },
                    list: {
                        cate: 'Coding',
                        cate_tag: 'Tech',
                        view: 'Preview',
                        view_close: 'Close Preview',
                        empty: 'none',
                        tag: 'Tags',
                        status: 'Status',
                        status_empty: 'Nothing'
                    },
                }
            }
        },
        mounted() {
            //我爱我的祖国
            if (!!cookie.get('ouorz_flag_cookie')) {
                this.flag = true;
            }

            //获取分类
            axios.get('https://www.ouorz.com/wp-json/wp/v2/categories?exclude=1,58')
                .then(response => {
                    this.cates = response.data;
                })
                .then(() => {
                    this.loading_cates = false;

                    //获取标签
                    axios.get('https://www.ouorz.com/wp-json/wp/v2/tags?orderby=count&order=desc&per_page=15')
                        .then(response => {
                            this.tages = response.data;
                        }).then(() => {
                            this.loading_tages = false;
                        });

                });


            //判断内容版本
            if (window.english) {
                var version = '&categories=74';
                this.version = '&categories=74';
                this.word = this.word_english;
            } else {
                var version = '&categories_exclude=5,2,74';
                this.version = '&categories_exclude=5,2,74';
                this.word = this.word_chinese;
            }

            //判断 cookie 说明阅读
            if (parseInt(cookie.get('ouorz_read_cookie')) !== 1) {
                this.notice.visible = true;
            }

            //获取文章列表
            axios.get('https://www.ouorz.com/wp-json/wp/v2/posts?per_page=10&page=' + paged + version)
                .then(response => {
                    this.posts = response.data
                })
                .catch(e => {
                    this.errored = false
                })
                .then(() => {
                    this.loading = false;
                    paged++; //加载完1页后累加页数
                    //加载完文章列表后监听滑动事件
                    $(window).scroll(function () {
                        var scrollTop = $(window).scrollTop();
                        var scrollHeight = $('.bottom').offset().top - 800;
                        if (scrollTop >= scrollHeight) {
                            if (click == 0) { //接近底部加载一次新文章
                                $('#scoll_new_list').click();
                                click++; //加载次数计次
                            }
                        }
                    });

                })
        },
        methods: { //定义方法
            new_page: function () { //加载下一页文章列表
                $('#view-text').html('-&nbsp;Loading&nbsp;-');
                axios.get('https://www.ouorz.com/wp-json/wp/v2/posts?per_page=10&page=' + paged + this.version)
                    .then(response => {
                        if (response.data.length !== 0) { //判断是否最后一页
                            $('#view-text').html('-&nbsp;Posts List&nbsp;-');
                            this.posts.push.apply(this.posts, response.data); //拼接在上一页之后
                            click = 0;
                            paged++;
                        } else {
                            $('#view-text').html('-&nbsp;All Posts&nbsp;-');
                            $('.bottom h5').html('No more posts O__O "…').css({
                                'background': '#fff',
                                'color': '#999'
                            });
                            this.loading_css = 'none';
                        }
                    }).catch(e => {
                        $('#view-text').html('-&nbsp;All Posts&nbsp;-');
                        $('.bottom h5').html('No more posts O__O "…').css({
                            'background': '#fff',
                            'color': '#999'
                        });
                        this.loading_css = 'none';
                    })
            },
            preview: function (postId) { //预览文章内容
                var previewingPost = $('.article-list-item .preview-p');
                if (!!previewingPost.length) { // 若有其它预览已打开,则自动收起
                    var previewingPostItemEl = previewingPost.parent('.article-list-item');
                    previewingPostItemEl.find('.list-show-btn').html(this.word.list.view);
                    previewingPostItemEl.find('.article-list-content').html(pre_post_con).removeClass('preview-p');
                    pre_post_con = '';
                    if (postId === pre_post_id) { // 若点击当前已打开文章的按钮
                        return;
                    }
                }

                $('#' + postId).html('<div uk-spinner></div>');
                axios.get('https://www.ouorz.com/wp-json/wp/v2/posts/' + postId)
                    .then(response => {
                        if (response.data.length !== 0) { //判断是否最后一页
                            $('#btn' + postId).html(this.word.list.view_close); //更改按钮
                            $('#' + postId).addClass('preview-p').html(response.data.content.rendered); //更改内容
                            pre_post_con = response.data.post_excerpt.nine; //保存摘录
                            pre_post_id = postId;
                            document.querySelectorAll('pre code').forEach((block) => {
                                hljs.highlightBlock(block);
                            });
                        } else {
                            $('#' + postId).html('Nothing Here');
                        }
                    });
            },
            discard_notice() {
                cookie.set('ouorz_read_cookie', 1);
                this.notice.visible = false;
            },
            my_beloved_china() {
                if (!!cookie.get('ouorz_flag_cookie')) {
                    cookie.del('ouorz_flag_cookie');
                    this.flag = false;
                } else {
                    cookie.set('ouorz_flag_cookie', 1);
                    this.flag = true;
                }
            },
        }
    });

});