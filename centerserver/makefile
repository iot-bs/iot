push:
	git add --all
	git commit -m 'test'
	git push
test:
	git push
kill:
	lsof -i:8090 |awk 'NR!=1{print $2}' |xargs kill -9
start:
	php /website/iot/centerserver/center.php start -h 0.0.0.0 -p 8090
startd:
	php /website/iot/centerserver/center.php start -d -h 0.0.0.0 -p 8090
