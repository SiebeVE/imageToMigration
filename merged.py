import argparse
import io
import os
import cv2
import numpy as np
import logging
import json

from google.cloud import vision

threshold1 = 85
threshold2 = 255
# Normal routines
imagePath = 'images/relation5.jpg'
img = cv2.imread(imagePath)
gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
cv2.imwrite('sofsqure2.png', gray)
ret, thresh = cv2.threshold(gray, threshold1, threshold2, cv2.THRESH_BINARY)
cv2.imwrite('sofsqure3.png', thresh)

# Remove some small noise if any.
dilate = cv2.dilate(thresh, None)
erode = cv2.erode(dilate, None)

# Find contours with cv2.RETR_CCOMP
hierarchy, contours, hierarchy = cv2.findContours(erode, cv2.RETR_CCOMP, cv2.CHAIN_APPROX_SIMPLE)

vision_client = vision.Client()

with io.open('sofsqure3.png', 'rb') as image_file:
# with io.open(imagePath, 'rb') as image_file:
    content = image_file.read()

image = vision_client.image(content=content)

texts = image.detect_text()
firstText = texts.pop(0)
# print(firstText.description)
# print('Founded texts:')
points = {
    "all": firstText.description,
    "text": {},
    "rectangle": []
}
for index, text in enumerate(texts):
    points["text"][str(index) + "$" + text.description] = []
    first = 1
    for point in text.bounds.vertices:
        pointCoordinates = [point.x_coordinate, point.y_coordinate]
        points["text"][str(index) + "$" + text.description].append(pointCoordinates)
        if first == 1:
            pts = np.array([pointCoordinates])
            first = 0
        else:
            point = np.array([pointCoordinates])
            pts = np.concatenate((pts, point), axis=0)
    cv2.polylines(img, [pts], True, (0, 0, 255))
    # print(text.description)

for i, cnt in enumerate(contours):
    # Check if it is an external contour and its area is more than 100
    # if hierarchy[0, i, 3] == -1 and cv2.contourArea(cnt) > 100:
    if hierarchy[0, i, 3] == -1 and cv2.contourArea(cnt) > 1000:
        x, y, w, h = cv2.boundingRect(cnt)
        cv2.rectangle(img, (x, y), (x + w, y + h), (0, 255, 0), 2)
        # print("----------------New rectangle " + str(i) + "----------------")
        # print("coordinate: (" + str(x) + ", " + str(y) + ")")
        # print("dimensions: (" + str(w) + ", " + str(h) + ")")
        points["rectangle"].append({
            "coordinates": [x, y],
            "dimensions": [w, h]
        })
        # print()

        m = cv2.moments(cnt)
        cx, cy = m['m10'] / m['m00'], m['m01'] / m['m00']
        cv2.circle(img, (int(cx), int(cy)), 3, 255, -1)

# cv2.imshow('img', img)
cv2.imwrite('sofsqure.png', img)
# print(points)
print(json.dumps(points))
# cv2.waitKey(0)
# cv2.destroyAllWindows()
