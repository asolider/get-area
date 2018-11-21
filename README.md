# 获取省市区分类数据，

	数据源: http://www.stats.gov.cn/tjsj/tjbz/tjyqhdmhcxhfdm/2017/index.html

# 初始化数据库表结构

    结构sql文件在：[sql初始化](database/migrations/area_table_init.sql)

# 执行步骤

1. 获取一级数据（省）： php artisan location:getprovince
2. 获取二级数据（市）： php artisan location:getcity
3. 获取三级数据（区/县）： php artisan location:getarea
4. 获取四级数据（乡镇）： php artisan location:gettown
5. 获取五级数据（行政村）： php artisan location:getstreet

