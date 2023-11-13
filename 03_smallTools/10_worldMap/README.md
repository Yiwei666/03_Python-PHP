# 项目功能


# 文件结构

```
01_worldMap.php          # 基于经纬度在世界地图中进行标注
02_ipToCity_ipapi.php    # 基于 https://ipapi.co/{$ip}/city/ 获取城市等信息
02_ipToCity.php          # 基于 https://ipinfo.io/{$ip}/json 获取相关信息

```




# 环境配置

02_ipToCity.php 输出示例，基于`https://ipinfo.io/{$ip}/json` 获取相关信息，Deadline信息 似乎不对，经纬度和城市信息基本是对的

```
Hello world

Your IP address is: 159.223.137.77

Your location is: US, North Bergen

The current time is: 2023-11-12 21:37:57

Deadline 11:30 has passed

Deadline 17:30 has passed

Deadline 22:0 has passed

Days until May 23rd, 2025: 557

Remaining time until May 23rd, 2025: 13370 hours (48133323 seconds)

City information: { lat: 40.8043, lon: -74.0121, name: 'North Bergen, US' }
```
