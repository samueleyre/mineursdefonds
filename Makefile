install:
	@docker-compose down
	@docker-compose build --force-rm
	@echo "Installation completed"

run:
	@docker-compose up -d
