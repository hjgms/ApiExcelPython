from re import findall
# from io import BytesIO
# from urllib.request import urlopen
from random import randint
from xlsxwriter import Workbook
from datetime import datetime
from os import path, remove
from PIL import Image


class ExcelGenerator:

    def __init__(self, filename=""):

        if filename == "":
            random = randint(0, 999)
            self.file_name = f"archive_{random}.xlsx"
        else:
            self.file_name = filename

        # create document
        self.workbook = Workbook(self.get_file_path())
        self.worksheet = self.workbook.add_worksheet()

        # cell formats
        self.date_format = self.workbook.add_format({'num_format': 'dd/mm/yy'})
        self.price_format = self.workbook.add_format({'num_format': '#,##0.00'})

        # array images
        self.array_images = []
        self.image_path = f"{path.dirname(__file__)}/../../tmp/img/"

    def write_document(self, doc):
        n = 1
        # write lines
        for items in doc:
            # write cols
            for obj in items["row"]:
                # print(obj)
                self.write_type(obj["value"], obj["type"], obj["pos"] + f"{n}")

            n += 1

        self.workbook.close()

        # delete images in tmp
        # self.clear_img_path()

    def write_type(self, value, type_write="string", pos="A1"):
        if type_write == "string":
            self.worksheet.write_string(pos, value)
        elif type_write == "integer":
            self.worksheet.write_number(pos, value)
        elif type_write == "price":
            self.worksheet.write_number(pos, value, self.price_format)
        elif type_write == "boolean":
            self.worksheet.write_boolean(pos, value)
        elif type_write == "date":
            date_time = datetime.strptime(value, '%Y-%m-%d')
            self.worksheet.write_datetime(pos, date_time, self.date_format)
        elif type_write == "image":
            self.set_image(pos, value)

    def set_image(self, pos="A1", file=""):
        # url = 'https://python.org/logo.png'
        # images_path = self.image_path
        # image_data = BytesIO(urlopen(url).read())
        # path of image
        file = "python-logo.png"
        filename = self.image_path + file
        image = Image.open(filename)

        # resize file image into 180 px height
        w, h = image.size
        # scale = h / 180
        # new_h = int(h / scale)
        # new_w = int(w / scale)

        # image.resize((new_w, new_h))

        # url_split = url.split('/')
        # filename = url_split[len(url_split) - 1]
        # file_split = filename.split('.')
        # filename = images_path + filename
        # image.save(filename, f"{file_split[1]}")
        # self.array_images.append(filename)

        row = int(''.join(findall(r'\d+', pos)))

        self.worksheet.set_row(row - 1, h * 0.3)
        self.worksheet.insert_image(pos, filename, {'x_scale': 0.3, 'y_scale': 0.3})
        # self.worksheet.insert_image(pos, image_data, {'image_data': image_data, 'x_scale': 0.3, 'y_scale': 0.3})

    def get_file_path(self):
        return f"{path.dirname(__file__)}/../../tmp/{self.file_name}"

    def clear_img_path(self):
        for img in self.array_images:
            if path.exists(img):
                remove(img)
