import argparse
import io
import os

from google.cloud import vision


def detect_text(path):
    """Detects text in the file."""
    vision_client = vision.Client()

    with io.open(path, 'rb') as image_file:
        content = image_file.read()

    image = vision_client.image(content=content)

    texts = image.detect_text()
    print('Texts:')
    for text in texts:
        print(text.bounds.vertices)
        print(text.description)

detect_text('images/simple.jpg')
