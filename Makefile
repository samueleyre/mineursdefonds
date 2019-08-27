install:
	@docker-compose down
	@docker-compose build --force-rm
	@git clone git@github.com:samueleyre/mineursdefonds.git src/wp-content/themes/mineursdefonds
	@echo "Installation completed"

run:
	@docker-compose up -d
