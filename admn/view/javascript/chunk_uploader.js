(function() {

	const MAX_CHUNK = 200000; // 200 KB her parça

	// Sadece büyük formlar için kullanılacak
	window.sendChunkedForm = function(formSelector, callback) {

		var $form = $(formSelector);
		if (!$form.length)
			return; // form yoksa çık

		var form = $form[0]; // FormData için plain DOM element
		const formData = new FormData(form);

		// FormData → JSON
		let json = {};
		formData.forEach((v, k) => { json[k] = v; });

		const raw = JSON.stringify(json);

		if (raw.length <= MAX_CHUNK) {
			// Küçük form → normal submit
			if (typeof callback === 'function')
				callback();
			return;
		}

		// Parçala
		let chunks = [];
		for (let i = 0; i < raw.length; i += MAX_CHUNK) {
			chunks.push(raw.substring(i, i + MAX_CHUNK));
		}

		let index = 0;

		function sendNextChunk()
		{
			fetch('index.php?route=tool/chunk/save&user_token=' + oc.token, {
				method: 'POST',
				body: JSON.stringify({
					index,
					total: chunks.length,
					data: chunks[index]
				}),
				headers: { 'Content-Type': 'application/json' }
			})
			.then(r => r.json())
			.then(j => {
				if (j.status === 'part') {
					index++;
					sendNextChunk();
				} else if (j.status === 'done') {
					if (typeof callback === 'function')
						callback();
				}
			})
			.catch(err => console.error('Chunk send error:', err));
		}

		sendNextChunk();

	};

})();

