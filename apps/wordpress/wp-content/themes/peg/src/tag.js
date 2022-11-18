window.onload = function(){ //避免爆代码
        
    var click = 0; //初始化加载次数
    var paged = 1; //获取当前页数
    var incate = window.incate;
    
    /* 展现内容(避免爆代码) */
    $('.article-list').css('opacity','1');
    $('#header-div').css('opacity', '1');
    $('#header_info').css('opacity', '1');
    $('.top1').html(window.tag_name);
    $('.top2').html('In total: '+window.tag_count+ ' Posts');
    $('.cat-real').attr('style','display:inline-block');
    /* 展现内容(避免爆代码) */
    
    new Vue({ //axios获取顶部信息
        el : '#grid-cell',
        data() {
            return {
                flag: false,
                posts: null,
                cates: null,
                des: null,
                loading: true, //v-if判断显示占位符
                loading_des: true,
                errored: true,
                word:{},
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
                        back: '回到首页',
                        cate: '研究学习',
                        tag: '文章标签',
                        cate_tag: '技术',
                        empty: '空',
                        view: '全文速览',
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
                        back: 'Back to home',
                        cate: 'Coding',
                        cate_tag: 'Tech',
                        view: 'Preview',
                        empty: 'none',
                        tag: 'Tags',
                        status: 'Status',
                        status_empty: 'Nothing'
                    },
                }
            }
        },
        mounted () {

            //我爱我的祖国
            if (!!cookie.get('ouorz_flag_cookie')) {
                this.flag = true;
            }

            //判断内容版本
            if (window.english) {
                this.word = this.word_english;
            } else {
                this.word = this.word_chinese;
            }
                 //获取分类
                 axios.get('https://www.ouorz.com/wp-json/wp/v2/categories?exclude=1')
                 .then(response => {
                     this.des = response.data;
                 }).then(() => {
                    this.loading_des = false;
                 });
            
            //获取文章列表
            axios.get('https://www.ouorz.com/wp-json/wp/v2/posts?per_page=10&page='+paged+'&tags='+incate)
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
                $(window).scroll(function(){
　　                    var scrollTop = $(window).scrollTop();
　　                    var scrollHeight = $('.bottom').offset().top - 500;
　　                    if(scrollTop >= scrollHeight){
　　                        if(click == 0){ //接近底部加载一次新文章
　　　　                        $('#scoll_new_list').click();
　　　　                        click++; //加载次数计次
　　                        }
　　                    }
                });
                
            })
        },
        methods: { //定义方法
            my_beloved_china() {
                if (!!cookie.get('ouorz_flag_cookie')) {
                    cookie.del('ouorz_flag_cookie');
                    this.flag = false;
                } else {
                    cookie.set('ouorz_flag_cookie', 1);
                    this.flag = true;
                }
            },
            new_page : function(){ //加载下一页文章列表
                axios.get('https://www.ouorz.com/wp-json/wp/v2/posts?per_page=10&page='+paged+'&tags='+incate)
             .then(response => {
                 if(response.data.length !== 0){ //判断是否最后一页
                     this.posts.push.apply(this.posts,response.data); //拼接在上一页之后
                     click = 0;
                     paged++;
                 }else{
                     $('.bottom h5').html('暂无更多文章了 O__O "…').css({'background':'#fff','color':'#999'});
                     $('#load-over').css('display','none');
                 }
             }).catch(e => {
                 $('#view-text').html('-&nbsp;所有文章&nbsp;-');
                 $('.bottom h5').html('暂无更多文章了 O__O "…').css({'background':'#fff','color':'#999'});
                 $('#load-over').css('display','none');
             })
        }
            },
    });
    
    
}