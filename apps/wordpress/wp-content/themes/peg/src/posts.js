window.onload = function(){ //避免爆代码

    /* 展现内容(避免爆代码) */
    $('.article-list').css('opacity','1');
    $('#header-div').css('opacity', '1');
    $('#header_info').css('opacity', '1');
    $('.cat-real').attr('style','display:inline-block');
    /* 展现内容(避免爆代码) */
    
    new Vue({ //axios获取顶部信息
        el : '#grid-cell',
        data() {
            return {
                flag: false,
                posts: null,
                loading: true, //v-if判断显示占位符
                loading_des: false,
                last_year: 0,
                posts_array: [],
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
            //获取文章列表
            axios.get('https://www.ouorz.com/wp-json/wp/v2/posts?per_page='+window.post_count) //默认以发布时间排序
             .then(response => {
                 this.posts = response.data
             })
             .then(() => {
                 var k = -1;
                 var i = 0;
                 for(i=0;i<(this.posts).length;i++){ //遍历所有文章
                     if( ((this.posts[i].date.split('T'))[0].split('-'))[0] !== this.last_year ){ //当前文章发布年与上一篇不同
                         this.posts_array[k += 1] = []; //初始化数组
                         this.posts_array[k]['posts'] = []; //初始化 posts 数组
                         this.posts_array[k]['year'] = parseInt(((this.posts[i].date.split('T'))[0].split('-'))[0]); //增加年份
                         this.posts_array[k]['posts'][(this.posts_array[k]['posts']).length] = this.posts[i]; //增加文章
                         this.last_year = ((this.posts[i].date.split('T'))[0].split('-'))[0]; //赋值当前文章发布年份
                     }else{ //发布年份与上一篇相同
                        this.posts_array[k]['posts'][(this.posts_array[k]['posts']).length] = this.posts[i]; //增加文章
                     }
                 }
                 this.loading = false;
            })
        },
        methods: {
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
    
    
}