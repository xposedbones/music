<section id="album-listing">
	<ul class="clearfix">
		{{#each albums}}
		<li class="{{type}}">
			<img src="{{image_url}}" alt="">
			<div class="info">
				<div class="info_holder">
					<span class="artist">{{artist}}</span>
					<span class="album-name">{{name}}</span>
				</div>
				<div class="controls">
					
						<a href="#" class="icon-info"><span>Info</span></a>
						<a href="#" class="icon-love"><span>Favorite</span></a>
						<a href="#" class="icon-download"><span>download</span></a>
					
				</div>
			</div>
			
		</li>
		{{/each}}
		
	</ul>
</section>