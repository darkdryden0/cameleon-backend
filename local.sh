docker stop stockview_container
docker rm stockview_container
docker build -f Dockerfile-local -t stockview .
docker run -d -p 8000:8000 --name stockview_container -v $(pwd):/var/www/html stockview

