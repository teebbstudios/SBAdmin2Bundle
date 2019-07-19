# SBAdmin2Bundle
代码参考SonataAdminBundle.


完成了后台首页Dashboard和边栏的功能。


因项目需要先暂停开发，其他功能后期再增加。


```yaml
#后台基本配置 path： config/packages/teebb_sb_admin2.yaml

teebb_sb_admin2:
    logo_text: 'TEEBBADMIN<sup>2</sup>'                             
    options:
        logo_mode: both
    design:
        sidebar_bg_class: bg-gradient-primary

    dashboard:
        heading:                                                
            link:
                link_route: teebb_sbadmin2_dashboard
                link_title: add_content
        groups:                                                     #边栏菜单
            content:                                
                news:
                    label:              abab
                    icon:               fa-edit
                    items:
                        - route:        teebb_sbadmin2_dashboard
                          label:        Blog1
                        - route:        teebb_sbadmin2_dashboard
                          route_params: { articleId: 3 }
                          label:        Article2
                news2:
                    label:              abab2
                    icon:               fa-edit
                    items:
                        - route:        teebb_sbadmin2_dashboard
                          label:        Blog1
                        - route:        teebb_sbadmin2_dashboard
                          route_params: { articleId: 3 }
                          label:        Article2

#            provider:
#                my_group:
#                    provider:        '@app.menu_provider'
#                    icon:            'fa-edit'

        blocks:
            -   position: left
                type: sonata.block.service.text
                class: col-md-6
                settings:
                    content: >
                        <h2>Welcome to the Sonata Admin</h2>
                        <p>This is a <code>sonata.block.service.text</code> from the Block
                        Bundle, you can create and add new block in these area by configuring
                        the <code>sonata_admin</code> section.</p> <br/> For instance, here
                        a RSS feed parser (<code>sonata.block.service.rss</code>):


imports:
    - { resource: teebb_sbadmin2/ }


```