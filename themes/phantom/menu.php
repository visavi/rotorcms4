<!-- Menu -->
<nav id="menu">
	<h2>Меню</h2>

	<ul>
		<li><a href="/forum/">Форум</a></li>
		<li><a href="/book/">Гостевая</a></li>
		<li><a href="/news/">Новости</a></li>
		<li><a href="/load/">Скрипты</a></li>
		<li><a href="/blog/">Блоги</a></li>


<?php if (is_user()): ?>
	<?php if (is_admin()): ?>

		<?php if (stats_spam()>0): ?>
			<li><a href="/admin/spam.php"><span style="color:#ff0000">Спам!</span></a></li>
		<?php endif; ?>

		<?php if ($udata['users_newchat']<stats_newchat()): ?>
			<li><a href="/admin/chat.php"><span style="color:#ff0000">Чат</span></a></li>
		<?php endif; ?>

			<li><a href="/admin/">Панель</a></li>
	<?php endif; ?>

	<li><a href="/pages/index.php?act=menu">Меню</a></li>
	<li><a href="/input.php?act=exit">Выход</a></li>

<?php else: ?>
	<li><a href="/pages/login.php">Авторизация</a></li>
	<li><a href="/pages/registration.php">Регистрация</a></li>
<?php endif; ?>



	</ul>
</nav>
<!-- Main -->
