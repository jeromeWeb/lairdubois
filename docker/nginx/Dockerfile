FROM nginx:1.11

# Remove default config
RUN rm /etc/nginx/conf.d/default.conf

# Add custom config
COPY nginx.conf /etc/nginx/nginx.conf
COPY symfony.conf /etc/nginx/sites-enabled/symfony.conf

# Set user for permissions
RUN usermod -u 1000 www-data