import cv2
import numpy as np
import logging

# Normal routines
img = cv2.imread('images/simple.jpg')
gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
cv2.imwrite('sofsqure2.png', gray)
ret, thresh = cv2.threshold(gray, 126, 255, cv2.THRESH_BINARY)
cv2.imwrite('sofsqure3.png', thresh)

# Remove some small noise if any.
dilate = cv2.dilate(thresh, None)
erode = cv2.erode(dilate, None)

# Find contours with cv2.RETR_CCOMP
hierarchy, contours, hierarchy = cv2.findContours(erode, cv2.RETR_CCOMP, cv2.CHAIN_APPROX_SIMPLE)

for i, cnt in enumerate(contours):
    # Check if it is an external contour and its area is more than 100
    # if hierarchy[0, i, 3] == -1 and cv2.contourArea(cnt) > 100:
    if hierarchy[0, i, 3] == -1 and cv2.contourArea(cnt) > 1000:
        x, y, w, h = cv2.boundingRect(cnt)
        cv2.rectangle(img, (x, y), (x + w, y + h), (0, 255, 0), 2)
        print("----------------New rectangle "+str(i)+"----------------")
        print("coordinate: (" + str(x) + ", " + str(y) + ")")
        print("dimensions: (" + str(w) + ", " + str(h) + ")")
        print()

cv2.imshow('img', img)
cv2.imwrite('sofsqure.png', img)
cv2.waitKey(0)
cv2.destroyAllWindows()
