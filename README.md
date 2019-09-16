# SBAdmin2Bundle
代码参考SonataAdminBundle.


完成了后台首页功能。


```yaml
#后台基本配置 path： config/packages/teebb_sb_admin2.yaml

teebb_sb_admin2:
    logo_text: 'TEEBBADMIN<sup>2</sup>'
    options:
        search:
            admin: 'App\Admin\ArticleAdmin'
            placeholder: 'Search Article'
            property: ['title', 'body']
            roles: []

        logo_mode: both
    design:
        sidebar_bg_class: bg-gradient-primary

    security:
        handler: teebb.sbadmin2.security.handler.role
        role_super_admin: ROLE_SUPER_ADMIN

    dashboard:
        heading:
            link:
                link_route: teebb_sbadmin2_dashboard
                link_title: add_content
        groups:
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

        blocks:
#            -   position: top
#                type: sonata.block.service.text
#                class: col-md-6
#                settings:
#                    content: >
#                        <h2>Welcome to the Sonata Admin</h2>
#                        <p>This is a <code>sonata.block.service.text</code> from the Block
#                        Bundle, you can create and add new block in these area by configuring
#                        the <code>sonata_admin</code> section.</p> <br/> For instance, here
#                        a RSS feed parser (<code>sonata.block.service.rss</code>):

            -   position: top
                type: teebb.sbadmin2.block.count    #block service type
                class: col-xl-3 col-md-6 mb-4       #block class
                roles: []
                settings:
                    admin: App\Admin\ArticleAdmin       #block admin. admin 对应的entity 应含有 createAt 和 updateAt 两个时间属性。
                    label: 内容量(最近一月)               #block label
                    translation_domain: TeebbSBAdmin2Bundle
                    icon: fas fa-file-alt               #block icon
                    border: border-left-primary         #block border
                    #property: updateAt                 #admin query object property. 要查询的Admin Entity的属性
                    duration: -1 month                  #The count of content in a duration, must earlier than now. see php strtotime. default: all
                    #template:                          #default template @TeebbSBAdmin2/Block/count_block.html.twig

imports:
    - { resource: teebb_sbadmin2/ }


```

```yaml
# admin配置 
teebb_sb_admin2:
    admins:
        App\Admin\ArticleAdmin:
            entity: App\Entity\Article
            controller: ~

            group: 'default'                #用于side 菜单显示的分组
            label: 'Article'                #用于side 菜单显示的项目名称
            icon: 'fa-trash'                #side菜单项的icon
            priority: 0

            hide_sidebar: false             #是否在边栏菜单中显示
            #            roles: ['ROLE_USER']

            action_type: item

            title: ~                        #content heading title 和 title

            form:                           #此项为必须设置项，用于设置 create edit 表单的字段。
                fields:
                    - {property: 'title', label: 'Title', type: 'Symfony\Component\Form\Extension\Core\Type\TextType', options: {  } }
                    - {property: 'body', label: 'Body', options: {  } }
                    - {property: 'category', label: 'Category',  options: { class: 'App\Entity\Category', choice_label: 'name' } }
                    - {property: 'createAt', label: 'createAt',  options: { attr: { class: 'col-12 col-md-6'},widget: 'single_text' } }
                    - {property: 'updateAt', label: 'updateAt',  options: { attr: { class: 'col-12 col-md-6'},widget: 'single_text' } }

            create:
                permission:
                    name: 'Article Create'
                    description: 'Article Create'
                    roles: []

            edit:
                permission:
                    name: 'Article Edit'
                    description: 'Article Edit'
                    roles: []

            list:                           #列表
                permission:
                    name: 'Article List'
                    description: 'Article List'
                    roles: []

                fields:                     #list显示的字段 sortable 是否表头可排序
                    - { property: 'title', action: 'edit', label: 'Title', class: '', sortable: true }
                    - { property: 'category.name', label: 'Category' }
                    - { property: 'body', label: 'Body' }

                actions:                    #列表的操作actions
                    - {name: 'edit', class: 'btn-primary', icon: 'fa fa-pen', roles: []}
                    - {name: 'delete', class: 'btn-danger', icon: 'fas fa-trash',hide: true, roles: []}

                filters:                    #过滤器 过滤的字段 row_class: 添加class到form行
                    - {property: 'title', label: 'title', roles: [], type: 'Symfony\Component\Form\Extension\Core\Type\TextType',
                       options: { attr: { row_class: 'col-12 col-md-6 mb-2 mb-md-0'} } }
                    - {property: 'category', label: 'Category', roles: [],
                       options: { attr: { row_class: 'col-12 col-md-2 mb-2 mb-md-0'}, placeholder: 'Select Category', class: 'App\Entity\Category', choice_label: 'name' } }

                batch_actions:                       #批量操作action
                    - {action: 'delete', option_label: 'Batch Delete', roles: []}


            delete:
                permission:
                    name: 'Article Edit'
                    description: 'Article Edit'
                    roles: []


        App\Admin\CategoryAdmin:
            entity: App\Entity\Category
            controller: ~

#            children: 'App\Admin\ArticleAdmin'   #子admin，例如 CategoryAdmin 的子Admin是 ArticleAdmin， 则生成的URL例子: /category/{id}/article/(id}
#            map_property: 'category'

            group: 'default'                #用于side 菜单显示的分组
            label: 'Category'                #用于side 菜单显示的项目名称
            icon: 'fa-check'               #side菜单项的icon
            priority: 0

            title: ~                   #content heading title 和 title

            form:                           #此项为必须设置项，用于设置 create edit 表单的字段。
                fields:
                    - {property: 'name', label: 'Name', options: {  } }

            create:
                permission:
                    name: 'Category Create'
                    description: 'Category Create'
                    roles: []

            edit:
                permission:
                    name: 'Category Edit'
                    description: 'Category Edit'
                    roles: []

            list:                           #列表
                permission:
                    name: 'Category List'
                    description: 'Category List'
                    roles: []

                fields:                     #list显示的字段 sortable 是否表头可排序
                    - { property: 'name', action: 'edit', label: 'Category Name', class: '', sortable: true }

                actions:                    #列表的操作actions
                    - {name: 'edit', class: 'btn-primary', icon: 'fa fa-pen', roles: []}
                    - {name: 'delete', class: 'btn-danger', icon: 'fas fa-trash', roles: []}

                filters:                    #过滤器 过滤的字段 row_class: 添加class到form行
                    - {property: 'name', label: 'name', roles: [], type: 'Symfony\Component\Form\Extension\Core\Type\TextType',
                       options: { attr: { row_class: 'col-12 col-md-6 mb-2 mb-md-0'} } }

            #                batch_actions: []                      #批量操作action


            delete:
                permission:
                    name: 'Category Edit'
                    description: 'Category Edit'
                    roles: []


```