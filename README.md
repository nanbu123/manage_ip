manage_ip
=========

* 概要

manage ip and rack database

某所で使われていたラックとIPアドレスを管理するデータベースです。
ソースがもったいないので公開します。

202503
EUCからUTF8に変更して動くのが分かりました。（setupは直ってないかも）
- dbの中身がUTF8
- phpファイルがUTF8
- ブラウザでの表示がUTF8

* 動作環境
PHP5.2の環境を作るのがとても苦労するのでdockerで構築

- docker
1. mariadb55 on docker
2. PHP5.2 on tommylau/php-5.2
3. nginx on tommylau/nginx


