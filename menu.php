<table class="none" valign="top">
  <tr>
    <td><a href="./index.php">検索画面</a></td>
  </tr>
  <tr>
    <td><a href="./company_list.php">会社一覧</a></td>
  </tr>
  <tr><td>&nbsp</td></tr>
  <tr>
    <td><a href="./ip_list.php">IPアドレス一覧</a></td>
  </tr>
<?php if (check_auth('add')) { ?>
  <tr>
    <td><a href="./ip_add.php">IPまとめ登録</a></td>
  </tr>
<?php } ?>
  <tr><td>&nbsp</td></tr>
  <tr>
    <td><a href="./rack_list.php">ラック一覧</a></td>
  </tr>
<?php if (check_auth('add')) { ?>
  <tr>
    <td><a href="./rack_add.php">ラック登録</a></td>
  </tr>
<?php } ?>
  <tr><td>&nbsp</td></tr>
  <tr>
    <td><a href="./patch_list.php">パッチパネル一覧</a></td>
  </tr>
<?php if (check_auth('add')) { ?>
  <tr>
    <td><a href="./patch_add.php">パッチパネル登録</a></td>
  </tr>
<?php } ?>
  <tr><td>&nbsp</td></tr>
  <tr>
    <td><a href="./breaker_list.php">ブレーカー一覧</a></td>
  </tr>
<?php if (check_auth('add')) { ?>
  <tr>
    <td><a href="./breaker_add.php">ブレーカー登録</a></td>
  </tr>
  <tr>
    <td><a href="./breaker_csv_up.php">ブレーカーCSV登録</a></td>
  </tr>
<?php } ?>
  <tr><td>&nbsp</td></tr>
  <tr>
    <td><a href="./router_only_list.php">ルータ一覧</a></td>
  </tr>
  <tr>
    <td><a href="./router_list.php">ルータポート一覧</a></td>
  </tr>
<?php if (check_auth('add')) { ?>
  <tr>
    <td><a href="./router_add.php">ルータポート登録</a></td>
  </tr>
<?php } ?>
  <tr><td>&nbsp</td></tr>
<?php if (check_auth('add')) { ?>
  <tr>
    <td><a href="./user_list.php">ユーザ一覧</a></td>
  </tr>
<?php } ?>
  <tr>
    <td><a href="./user_password.php">パスワード変更</a></td>
  </tr>
  <tr><td>&nbsp</td></tr>
  <tr>
    <td><a href="./logout.php">ログアウト</a></td>
  </tr>
</table>