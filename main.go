package main

import (
	"log"

	"github.com/gofiber/fiber/v2"
	"github.com/gofiber/fiber/v2/middleware/cors"
	"github.com/gofiber/fiber/v2/middleware/logger"
)

func main() {
	app := fiber.New()
	app.Use(cors.New(cors.Config{
		AllowOrigins:     "http://localhost:5173, http://localhost:8899",
		AllowHeaders:     "Content-Type,X-Some-Token",
		AllowCredentials: true,
	}))
	app.Use(logger.New())

	app.All("/p", RouteProxy())
	app.Post("/cdn/upload", RouteCdnUpload())

	log.Fatal(app.Listen(":8899"))
}
