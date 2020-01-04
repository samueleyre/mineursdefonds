build:
	@docker-compose down
	@docker-compose build --force-rm
	@echo "Docker built"

run:
	@docker-compose up -d
