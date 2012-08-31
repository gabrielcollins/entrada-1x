var grow;
function growPic(picture, unzoom) {
	if (picture.width == 72 && !grow) {
		$$('.zoom').each(function (e) { e.innerHTML = ''; });
		picture.style.zIndex = 7; 
		new Effect.Scale(picture, 300, 
		{
				scaleMode: 
				{ 
					originalHeight: 100, 
					originalWidth: 72 
				},
				afterFinish: function() {
					grow = true;
					unzoom.innerHTML = '-';
				}
		}); 
		return false;
	}
}

function shrinkPic(picture, unzoom) {
	if (picture.width > 72 && picture.style.zIndex > 5) {
		unzoom.innerHTML = '';
		new Effect.Scale(picture, 100, 
		{
			scaleFrom: (picture.width / 72 * 100), 
			scaleMode: 
			{ 
				originalHeight: 100, 
				originalWidth: 72 
			},
			afterFinish: function() {
				picture.style.zIndex = 5;
				$$('.zoom').each(function (e) { e.innerHTML = '+'; });
				grow = false;
			}
		});
		
		return false;}
}