package main

import (
	"errors"

	"github.com/gofiber/fiber/v2"
	"github.com/gofiber/fiber/v2/middleware/proxy"
)

func RouteProxy() func(*fiber.Ctx) error {
	return func(c *fiber.Ctx) error {
		url := c.Query("url", "")

		if len(url) == 0 {
			return errors.New("Invalid request, missing qs")
		}

		c.Request().URI().QueryArgs().Del("url")

		if err := proxy.Do(c, url); err != nil {
			return err
		}

		reqs := c.GetReqHeaders()
		if ok := reqs["Origin"]; ok != nil {
			c.Response().Header.Add("Access-Control-Allow-Origin", reqs["Origin"][0])
		}

		// Remove Server header from response
		c.Response().Header.Del(fiber.HeaderServer)
		return nil
	}
}
