# Welcome to Docker!
#### If you're reading this, it means that we've somehow been able to get this amazing technology into your hands, and (hopefully) will be able to speed up your development time by an incredible amount.

# So...what is it?
So the best way I've seen to explain Docker is using the analogy of containers because, well, it is a container. (Warning:We'll be saying 'container' a lot)

So back in the day when the cargo industry was getting off the ground, we quickly realized that if we wanted to move a lot of things with different shipping requirements, there would be a lot of hassle involved in making sure all of the requirements were met to get these products moving. Then came along the shipping container, and from there everything became standardized. With a container, you keep everything inside the container that you need, and when you need to change locations, that container just moves as a whole unit.

![Beautiful ain't it?](https://upload.wikimedia.org/wikipedia/commons/thumb/9/96/20_ft_Dry_Container_%28DV%29_-_RAL_5010.jpg/320px-20_ft_Dry_Container_%28DV%29_-_RAL_5010.jpg)
### No dependencies.

### No 'it works on my machine'.

### No hassle.

# Ok, so how do I use it?
1. Install [Docker](www.docker.com)
    * Optional: If you're on linux you'll need to install Docker-Compose separately. Otherwise, carry on.
3. Download the files and open a terminal in that folder
4. Run `docker-compose up`. Hopefully you'll get a message saying `apache2 -D FOREGROUND`
5. Visit `LOCALHOST:8080/wp-admin/install.php` to install Wordpress.
    * Depending on your machine, LOCALHOST might be a different IP. For example, a windows box will be 192.168.99.100 in most cases. Other machines might use 0.0.0.0 or simply localhost.
6. After Wordpress is installed, run `docker exec -it wordpress_wordpress_1 /var/www/html/postscript.sh` to install all the necessary themes and plugins.
    * If you get an error, you may need to run `docker exec -it wordpress_wordpress_1 chmod +x /var/www/html/postscript.sh`
7. That's it. No, seriously, That's it.

# What if something breaks?
If something breaks during the above process, let me know.

If something breaks and you want to reset your environment:
1. `docker-compose down`
2. Repeat the above steps to rebuild.
