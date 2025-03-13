package main

import (
	"context"
	"fmt"
	"os"
	"path/filepath"

	"log"

	"github.com/aws/aws-sdk-go-v2/aws"
	"github.com/aws/aws-sdk-go-v2/config"
	"github.com/aws/aws-sdk-go-v2/credentials"
	"github.com/aws/aws-sdk-go-v2/service/s3"
	"github.com/gofiber/fiber/v2"
)

// REFERENCES:
// - https://developers.cloudflare.com/r2/examples/aws/aws-sdk-go/
// - https://docs.aws.amazon.com/sdk-for-go/v2/developer-guide/go_s3_code_examples.html
// - https://github.com/aws/aws-sdk-go-v2

const BUCKET_NAME = "wmh-app"
const ACCOUNT_ID = "07b0ef5b9b2903312ba4e9c519285a3d"
const BUCKET_DOMAIN = "https://cdn.sydl.nl"

func RouteCdnUpload() func(*fiber.Ctx) error {
	var accessKeyId = os.Getenv("CF_ACCESS_KEY")
	var accessKeySecret = os.Getenv("CF_ACCESS_SECRET")

	return func(c *fiber.Ctx) error {
		token := c.GetReqHeaders()["X-Some-Token"]
		if len(token) == 0 || token[0] != os.Getenv("APP_TOKEN") {
			c.JSON(fiber.Map{
				"status": false,
				"error":  "Forbidden",
			})
			return nil
		}

		if len(accessKeyId) == 0 || len(accessKeySecret) == 0 {
			c.JSON(fiber.Map{
				"status": false,
				"error":  "missing",
			})
			return nil
		}

		image, err := c.FormFile("image")
		filename := c.FormValue("filename")
		if err != nil {
			log.Println("[ERROR] Upload error: ", err)
			c.JSON(fiber.Map{
				"status": false,
				"error":  fmt.Sprintf("Error: %s", err),
			})
			return nil
		}

		file, err := image.Open()
		if err != nil {
			log.Println("[ERROR] Cannot read file: ", err)
			c.JSON(fiber.Map{
				"status": false,
				"error":  fmt.Sprintf("Error open file: %s", err),
			})
			return nil
		}
		defer file.Close()

		// define configuration for obj
		ext := filepath.Ext(image.Filename)
		objKey := filename + ext
		mime := image.Header["Content-Type"][0]

		log.Printf("UPLOAD objkey: '%s' mime: '%s' \n", objKey, mime)

		/// craete connection with AWS/Cloudflare bucket
		cfg, err := config.LoadDefaultConfig(context.TODO(),
			config.WithCredentialsProvider(credentials.NewStaticCredentialsProvider(accessKeyId, accessKeySecret, "")),
			config.WithRegion("auto"),
		)

		if err != nil {
			log.Println("[ERROR] creating AWS/Cloudflare API: ", err)
			c.JSON(fiber.Map{
				"status": false,
				"error":  fmt.Sprintf("Error creating AWS/Cloudflare: %s", err),
			})
			return nil
		}

		endpoint := fmt.Sprintf("https://%s.r2.cloudflarestorage.com", ACCOUNT_ID)
		client := s3.NewFromConfig(cfg, func(o *s3.Options) {
			o.BaseEndpoint = aws.String(endpoint)
		})

		_, err = client.PutObject(context.TODO(), &s3.PutObjectInput{
			Key:         aws.String(objKey),
			Bucket:      aws.String(BUCKET_NAME),
			Body:        file,
			ContentType: aws.String(mime),
		})
		if err != nil {
			log.Println("[ERROR] putting object: ", err)
			c.JSON(fiber.Map{
				"status": false,
				"error":  fmt.Sprintf("Error uploading image: %s", err),
			})
			return nil
		}

		c.JSON(fiber.Map{
			"status":   true,
			"filename": fmt.Sprintf("%s/%s", BUCKET_DOMAIN, objKey),
			"mime":     mime,
		})
		return nil
	}
}
