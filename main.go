package main

import (
	"log"

	"github.com/gofiber/fiber/v2"
	"github.com/gofiber/fiber/v2/middleware/logger"
	"github.com/gofiber/fiber/v2/middleware/proxy"
)

func main() {
	app := fiber.New()
	app.Use(logger.New())

	app.All("/p", func(c *fiber.Ctx) error {
		url := c.Query("url")

		c.Request().URI().QueryArgs().Del("url")

		if err := proxy.Do(c, url); err != nil {
			return err
		}

		// Remove Server header from response
		c.Response().Header.Del(fiber.HeaderServer)
		return nil
	})

	log.Fatal(app.Listen(":8899"))
}
