from re import findall
from random import randint
from xlsxwriter import Workbook
from datetime import datetime
from os import path
from PIL import Image


class ExcelGenerator:

    def __init__(self):
        random = randint(0, 999)
        self.file_name = f"archive_{random}.xlsx"

        # create document
        self.workbook = Workbook(self.get_file_path())
        self.worksheet = self.workbook.add_worksheet()

        # cell formats
        self.date_format = self.workbook.add_format({'num_format': 'dd/mm/yy'})
        self.price_format = self.workbook.add_format({'num_format': '#,##0.00'})

        # image
        self.image_path = f"{path.dirname(__file__)}/../../tmp/img/"

        # progress
        # self.progress = 0
        # self.rows_size = 0

    def set_filename(self, filename=""):
        self.file_name = filename
        self.workbook.filename = self.get_file_path()

    def write_document(self, doc):

        # write lines
        for obj in doc:
            # write cols
            self.write_type(obj["value"], obj["type"], obj["pos"])

        self.workbook.close()

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
        # path of image
        filename = self.image_path + file
        image = Image.open(filename)

        # resize row of image
        w, h = image.size

        row = int(''.join(findall(r'\d+', pos)))

        self.worksheet.set_row(row - 1, h * 0.3)
        self.worksheet.insert_image(pos, filename, {'x_scale': 0.3, 'y_scale': 0.3})

    def get_file_path(self, name=""):
        if name == "":
            name = self.file_name

        # exemple path
        return f"{path.dirname(__file__)}/../../tmp/{name}"

