package main

import (
	"errors"
	"log"

	"github.com/gofiber/fiber/v2"
	"github.com/gofiber/fiber/v2/middleware/cors"
	"github.com/gofiber/fiber/v2/middleware/logger"
	"github.com/gofiber/fiber/v2/middleware/proxy"
)

func main() {
	app := fiber.New()
	app.Use(cors.New(cors.Config{
		AllowOrigins:     "http://localhost:5173, http://localhost:8899",
		AllowHeaders:     "Content-Type",
		AllowCredentials: true,
	}))
	app.Use(logger.New())

	app.All("/p", func(c *fiber.Ctx) error {
		url := c.Query("url", "")

		if len(url) == 0 {
			return errors.New("Invalid request, missing qs")
		}

		c.Request().URI().QueryArgs().Del("url")

		if err := proxy.Do(c, url); err != nil {
			return err
		}

		reqs := c.GetReqHeaders()
		c.Response().Header.Add("Access-Control-Allow-Origin", reqs["Origin"][0])
		// Remove Server header from response
		c.Response().Header.Del(fiber.HeaderServer)
		return nil
	})

	log.Fatal(app.Listen(":8899"))
}
