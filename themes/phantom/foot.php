						</div>
					</div>
				<!-- Footer -->
					<footer id="footer">
						<div class="inner">
							<section>
								<h2>Информация</h2>
								<?= show_counter() ?>
								<?= show_online() ?>
								<?= navigation() ?>

							</section>
<!-- 							<section>
								<h2>Follow</h2>


							</section> -->
							<ul class="copyright">
								<li>&copy; Copyright 2005-<?=date('Y')?> RotorCMS</li>
							</ul>

							<?= perfomance() ?>
						</div>
					</footer>

			</div>

		<!-- Scripts -->

			<?= include_javascript() ?>
			<script src="/themes/phantom/js/skel.min.js"></script>
			<script src="/themes/phantom/js/util.js"></script>
			<!--[if lte IE 8]><script src="/themes/phantom/js/ie/respond.min.js"></script><![endif]-->
			<script src="/themes/phantom/js/main.js"></script>

	</body>
</html>
